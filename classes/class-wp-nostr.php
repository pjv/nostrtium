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

class WP_Nostr {
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
    require_once PJV_WPNOSTR_DIR . 'classes/class-wp-nostr-metabox.php';
    require_once PJV_WPNOSTR_DIR . 'classes/class-wp-nostr-settings.php';
    $this->version  = PJV_WPNOSTR_VERSION;
    $this->settings = WP_Nostr_Settings::get_instance();
    $this->keyfile  = PJV_WPNOSTR_DIR . 'keyfile.key';

    if (is_admin()) {
      add_action('admin_enqueue_scripts', [$this, 'enqueue']);
      add_action('wp_ajax_pjv_wpn_post_note', [$this, 'post_note']);
    }
  }

  public function enqueue($page) {
    global $pagenow;
    if (($pagenow == 'post.php') && (get_post_type() == 'post')) {
      wp_enqueue_style('wpnostr-metabox.css', plugins_url('../css/wpnostr-metabox.css', __FILE__), [], PJV_WPNOSTR_VERSION);
      wp_register_script('wpnostr-metabox.js', plugins_url('../js/wpnostr-metabox.js', __FILE__), [], PJV_WPNOSTR_VERSION);
      $post_ID = isset($_GET['post']) ? $_GET['post'] : 0;
      $post = get_post($post_ID);
      $local_arr = [
        'ajaxurl'        => admin_url('admin-ajax.php'),
        'security'       => wp_create_nonce('wpnostr-ajax-nonce'),
        'post_id'        => $post_ID,
        'wpnostr_posted' => $post->_wpnostr_posted,
      ];
      wp_localize_script('wpnostr-metabox.js', 'wpnostr', $local_arr);
      wp_enqueue_script('wpnostr-metabox.js');
    }
    if (($pagenow == 'options-general.php' && $_GET['page'] == 'wp-nostr')) {
      wp_enqueue_style('fomantic-css', 'https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.9.2/semantic.min.css', [], PJV_WPNOSTR_VERSION);
      wp_enqueue_script('fomantic-js', 'https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.9.2/semantic.min.js', ['jquery'], PJV_WPNOSTR_VERSION);
      wp_enqueue_style('wpnostr-settings.css', plugins_url('../css/wpnostr-settings.css', __FILE__), [], PJV_WPNOSTR_VERSION);
      wp_register_script('wpnostr-settings.js', plugins_url('../js/wpnostr-settings.js', __FILE__), [], PJV_WPNOSTR_VERSION);
      if (get_option('wpnostr-enc-privkey')) {
        $private_key_set = true;
      } else {
        $private_key_set = false;
      }
      $local_arr = [
        'ajaxurl'         => admin_url('admin-ajax.php'),
        'security'        => wp_create_nonce('wpnostr-ajax-nonce'),
        'relays'          => $this->settings->relays,
        'private_key_set' => $private_key_set,
      ];
      wp_localize_script('wpnostr-settings.js', 'wpnostr', $local_arr);
      wp_enqueue_script('wpnostr-settings.js');
    }
  }

  public function post_note() {
    check_ajax_referer('wpnostr-ajax-nonce', 'security');
    $this->check_user();

    if (!get_option('wpnostr-enc-privkey')) {
      wp_send_json_error('You must enter your Nostr private key before you can post. Visit Settings -> WP Nostr.');
    }
    if (count($this->settings->relays) == 0) {
      wp_send_json_error('You have no relays defined to send to - visit Settings -> WP Nostr.');
    }

    $note = isset($_POST['note']) ? $_POST['note'] : null;
    if ($note == null) {
      wp_send_json_error('Something went wrong; did not receive any note text.');
    }

    $note = str_replace("\'", '’', $note);

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

    $result = $this->send_note($note);

    if ($result->sent) {
      if ($post_id > 0) {
        update_post_meta($post_id, '_wpnostr_posted', true);
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
    if (!get_option('wpnostr-relays')) {
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
      update_option('wpnostr-relays', $relays);
    }
  }

  public static function deactivate() {
    # code...
  }

  public function check_user() {
    if (!current_user_can(apply_filters('wpnostr_role', PJV_WPNOSTR_DEFAULT_USER_ROLE))) {
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
      $log .= "Sending to $url | ";

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

WP_Nostr::get_instance();