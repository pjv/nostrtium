<?php

if (!defined('WPINC')) {
  exit;
}

use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\Crypto as Symmetric;
use ParagonIE\HiddenString\HiddenString;
use swentel\nostr\Key\Key;

class WP_Nostr_Settings {
  private static $instance = null;
  private $title = 'WP Nostr';
  private $slug = 'wp-nostr';
  public $relays = [];
  public $encrypted_privkey = '';
  public $keyfile = '';

  // singleton
  public static function get_instance() {
    if (null == self::$instance) {
      self::$instance = new self;
    }

    return self::$instance;
  } // end get_instance;

  private function __construct() {
    global $pagenow;

    if (is_admin()) {
      add_action('admin_menu', [$this, 'setup_menu']);
      add_action('wp_ajax_pjv_wpn_save_relays', [$this, 'save_relays']);
      add_action('wp_ajax_pjv_wpn_save_private_key', [$this, 'save_private_key']);

      if ($pagenow == 'plugins.php') {
        add_filter("plugin_action_links_$this->slug/$this->slug.php", [$this, 'settings_link']);
      }
    }
    $this->relays = $this->get_relays();
    $this->encrypted_privkey = $this->get_encrypted_key();
    $this->keyfile = PJV_WPNOSTR_DIR . 'keyfile.key';
  }

  public function setup_menu() {
    add_options_page(
      $this->title,
      $this->title,
      apply_filters('wpnostr_role', PJV_WPNOSTR_DEFAULT_USER_ROLE),
      $this->slug,
      [$this, 'render_settings_page']
    );
  }

  public function settings_link($links) {
    $settings_link = '<a href="options-general.php?page=' . $this->slug . '">Settings</a>';
    array_unshift($links, $settings_link);

    return $links;
  }

  public function render_settings_page() {
    include PJV_WPNOSTR_DIR . 'views/settings-page.php';
  }

  public function save_private_key() {
    check_ajax_referer('wpnostr-ajax-nonce', 'security');
    $this->check_user();

    $nsec = isset($_POST['nsec']) ? $_POST['nsec'] : null;
    if ($nsec == null || (!str_starts_with($_POST['nsec'], 'nsec1'))) {
      wp_send_json_error('You must enter a private key in nsec format.');
    }

    # convert nsec to hex
    $key = new Key();
    $hex = $key->convertToHex($nsec);

    if ($hex == '') {
      wp_send_json_error('Not a valid nsec private key.');
    }

    # encrypt hex key
    $encryptionKey = KeyFactory::loadEncryptionKey($this->keyfile);
    $message = new HiddenString($hex);
    $enc = Symmetric::encrypt($message, $encryptionKey);

    # save encrypted key
    $this->set_encrypted_key($enc);
    wp_send_json_success();
  }

  private function set_relays(array $relays = []) {
    update_option('wpnostr-relays', $relays);
  }

  private function get_relays() {
    return get_option('wpnostr-relays');
  }

  private function set_encrypted_key(string $enc = '') {
    update_option('wpnostr-enc-privkey', $enc);
  }

  private function get_encrypted_key() {
    return get_option('wpnostr-enc-privkey');
  }

  public function check_user() {
    if (!current_user_can(apply_filters('wpnostr_role', PJV_WPNOSTR_DEFAULT_USER_ROLE))) {
      wp_die('Invalid permissions');
    }
  }

  public function save_relays() {
    check_ajax_referer('wpnostr-ajax-nonce', 'security');
    $this->check_user();

    $relays = isset($_POST['relays']) ? $_POST['relays'] : null;
    if ($relays == null) {
      wp_send_json_error('You must send a list of relays.');
    }

    $this->set_relays($relays);
    wp_send_json_success();
  }
}
WP_Nostr_Settings::get_instance();