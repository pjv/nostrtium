<?php

if (!defined('WPINC')) {
  exit;
}

use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\Crypto as Symmetric;
use swentel\nostr\Event\Event;
use swentel\nostr\Message\EventMessage;
use swentel\nostr\Relay\Relay;
use swentel\nostr\Sign\Sign;

class Nostrtium {
  private static $instance = null;
  public  $version         = '';
  private $settings        = null;
  public  $keyfile         = '';

  // singleton
  public static function get_instance() {
    if (null == self::$instance) {
      self::$instance = new self;
    }

    return self::$instance;
  } // end get_instance;

  private function __construct() {
    require_once PJV_NOSTRTIUM_DIR . 'classes/class-nostrtium-metabox.php';
    require_once PJV_NOSTRTIUM_DIR . 'classes/class-nostrtium-settings.php';
    $this->version  = PJV_NOSTRTIUM_VERSION;
    $this->settings = Nostrtium_Settings::get_instance();
    $this->keyfile  = PJV_NOSTRTIUM_DIR . 'keyfile.key';

    if (is_admin()) {
      add_action('admin_enqueue_scripts', [$this, 'enqueue']);
      add_action('wp_ajax_pjv_nostrtium_post_note', [$this, 'post_note']);
    }
  }

  public function enqueue($page) {
    global $pagenow;
    if (($pagenow == 'post.php') && (get_post_type() == 'post')) {
      wp_enqueue_style('modal-css', plugins_url('../css/jquery.modal.min.css', __FILE__), [], PJV_NOSTRTIUM_VERSION);
      wp_enqueue_script('modal-js', plugins_url('../js/jquery.modal.min.js', __FILE__), ['jquery'], PJV_NOSTRTIUM_VERSION);
      wp_enqueue_style('nostrtium-metabox.css', plugins_url('../css/nostrtium-metabox.css', __FILE__), [], PJV_NOSTRTIUM_VERSION);
      wp_register_script('nostrtium-metabox.js', plugins_url('../js/nostrtium-metabox.js', __FILE__), [], PJV_NOSTRTIUM_VERSION);
      $post_ID = intval($_GET['post']) ?? 0;
      $post = get_post($post_ID);
      $local_arr = [
        'ajaxurl'        => admin_url('admin-ajax.php'),
        'security'       => wp_create_nonce('nostrtium-ajax-nonce'),
        'post_id'        => $post_ID,
        'nostrtium_posted' => $post->_nostrtium_posted,
      ];
      wp_localize_script('nostrtium-metabox.js', 'nostrtium', $local_arr);
      wp_enqueue_script('nostrtium-metabox.js');
    }
    if (($pagenow == 'options-general.php' && $_GET['page'] == 'nostrtium')) {
      wp_enqueue_style('fomantic-css', plugins_url('../css/semantic.min.css', __FILE__), [], PJV_NOSTRTIUM_VERSION);
      wp_enqueue_script('fomantic-js', plugins_url('../js/semantic.min.js', __FILE__), ['jquery'], PJV_NOSTRTIUM_VERSION);
      wp_enqueue_style('nostrtium-settings.css', plugins_url('../css/nostrtium-settings.css', __FILE__), [], PJV_NOSTRTIUM_VERSION);
      wp_register_script('nostrtium-settings.js', plugins_url('../js/nostrtium-settings.js', __FILE__), [], PJV_NOSTRTIUM_VERSION);
      if (get_option('nostrtium-enc-privkey')) {
        $private_key_set = true;
      } else {
        $private_key_set = false;
      }
      $local_arr = [
        'ajaxurl'         => admin_url('admin-ajax.php'),
        'security'        => wp_create_nonce('nostrtium-ajax-nonce'),
        'relays'          => $this->settings->relays,
        'private_key_set' => $private_key_set,
      ];
      wp_localize_script('nostrtium-settings.js', 'nostrtium', $local_arr);
      wp_enqueue_script('nostrtium-settings.js');
    }
  }

  public function post_note() {
    check_ajax_referer('nostrtium-ajax-nonce', 'security');
    $this->check_user();

    if (!get_option('nostrtium-enc-privkey')) {
      wp_send_json_error('You must enter your Nostr private key before you can post. Visit Settings -> Nostrtium.');
    }
    if (count($this->settings->relays) == 0) {
      wp_send_json_error('You have no relays defined to send to - visit Settings -> Nostrtium.');
    }

    $note = wp_kses_post($_POST['note']) ?? null;
    if ($note == null) {
      wp_send_json_error('Something went wrong; did not receive any note text.');
    }

    // unescape ajaxified note text
    $note = strtr($note, [
      "\'" => "'",
      '\"' => '"',
      "\\\\" => "\\",
    ]);

    $post_id = intval($_POST['post_id']) ?? 0;

    $result = $this->send_note($note);

    if ($result->sent) {
      if ($post_id > 0) {
        update_post_meta($post_id, '_nostrtium_posted', true);
      }
      wp_send_json_success($result->log);
    } else {
      wp_send_json_error($result->log);
    }
  }

  public function activate() {
    # set up encryption key
    if (!file_exists($this->keyfile)) {
      $encKey = KeyFactory::generateEncryptionKey();
      KeyFactory::save($encKey, $this->keyfile);
    }

    // initial seed relays
    if (!get_option('nostrtium-relays')) {
      $relays = [
        'wss://relay.damus.io',
        'wss://nostr-pub.wellorder.net',
        'wss://relay.wellorder.net',
        'wss://nos.lol',
        'wss://nostr.mom',
        'wss://no.str.cr',
        'wss://relay.snort.social',
        'wss://nostr.milou.lol',
        'wss://nostr.bitcoiner.social',
        'wss://relay.nostrid.com',
        'wss://relay.nostrcheck.me',
        'wss://relayable.org',
      ];
      update_option('nostrtium-relays', $relays);
    }
  }

  public static function deactivate() {
    # code...
  }

  public function check_user() {
    if (!current_user_can(apply_filters('nostrtium_role', PJV_NOSTRTIUM_DEFAULT_USER_ROLE))) {
      wp_die('Invalid permissions');
    }
  }

  private function send_note(string $content = '') {
    $note = new Event();
    $note->setContent($content)->setKind(1);
    $signer = new Sign();
    $signer->signEvent($note, $this->decrypt_private_key($this->settings->encrypted_privkey));
    $eventMessage = new EventMessage($note);
    $log = '';

    $sent = false;
    foreach ($this->settings->relays as $url) {
      $log .= "Sent to $url | ";
      $relay = new Relay($url, $eventMessage);

      try {
        $result = $relay->send();
      } catch (Exception $exception) {
        $log .= '<span class="failure">' . $exception->getMessage() . '</span><br />';
        continue;
      }

      if ($result->isSuccess()) {
        $sent = true;
        $log .= '<span class="success">OK</span><br />';
      } else {
        $log .= '<span class="failure">' . $result->message() . '</span><br />';
      }
    }

    $r       = new stdClass;
    $r->sent = $sent;
    $r->log  = $log;

    return $r;
  }

  private function decrypt_private_key(string $enc_privkey = '') {
    $encryptionKey = KeyFactory::loadEncryptionKey($this->keyfile);
    $decrypted = Symmetric::decrypt($enc_privkey, $encryptionKey);

    return $decrypted->getString();
  }
}

Nostrtium::get_instance();