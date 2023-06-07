<?php

if (!defined('WPINC')) {
  exit;
}

use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\Crypto as Symmetric;
use ParagonIE\HiddenString\HiddenString;
use swentel\nostr\Key\Key;

class Nostrtium_Settings {
  private static $instance = null;
  private $title = 'Nostrtium';
  private $slug = 'nostrtium';
  public $relays = [];
  public $encrypted_privkey = '';
  public $keyfile = '';
  public $auto_publish_settings = null;

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
      add_action('wp_ajax_pjv_nostrtium_save_relays', [$this, 'save_relays']);
      add_action('wp_ajax_pjv_nostrtium_save_private_key', [$this, 'save_private_key']);
      add_action('wp_ajax_pjv_nostrtium_save_auto_publish', [$this, 'save_auto_publish_settings']);

      if ($pagenow == 'plugins.php') {
        add_filter("plugin_action_links_$this->slug/$this->slug.php", [$this, 'settings_link']);
      }
    }
    $this->relays                  = $this->get_relays();
    $this->encrypted_privkey       = $this->get_encrypted_key();
    $this->keyfile                 = PJV_NOSTRTIUM_STORAGE . 'keyfile.key';
    $this->auto_publish_settings   = $this->get_auto_publish_settings();
  }

  public function get_auto_publish_settings() {
    return get_option('nostrtium-auto-publish');
  }
  public function set_auto_publish_settings(array $ap) {
    update_option('nostrtium-auto-publish', $ap);
    $this->auto_publish_settings = $ap;
  }
  public function save_auto_publish_settings() {
    check_ajax_referer('nostrtium-ajax-nonce', 'security');
    $this->check_user();

    $ap = $_POST['apSettings'] ?? null;
    if ($ap == null) {
      wp_send_json_error('No auto publish settings received.');
    }

    foreach ($ap as $key => &$value) {
      $value = rest_sanitize_boolean($value);
    }

    $this->set_auto_publish_settings($ap);
    wp_send_json_success();
  }

  public function setup_menu() {
    add_options_page(
      $this->title,
      $this->title,
      apply_filters('nostrtium_role', PJV_NOSTRTIUM_DEFAULT_USER_ROLE),
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
    $ap_checked = $this->auto_publish_settings['autoPublish'] ? "checked" : "";
    $excerpt_checked = $this->auto_publish_settings['apExcerpt'] ? "checked" : "";
    $permalink_checked = $this->auto_publish_settings['apPermalink'] ? "checked" : "";
    $whole_post_checked = $this->auto_publish_settings['apWholePost'] ? "checked" : "";
    include PJV_NOSTRTIUM_DIR . 'views/settings-page.php';
  }

  public function save_private_key() {
    check_ajax_referer('nostrtium-ajax-nonce', 'security');
    $this->check_user();

    $nsec = sanitize_text_field($_POST['nsec']) ?? null;
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
    update_option('nostrtium-relays', $relays);
  }

  private function get_relays() {
    return get_option('nostrtium-relays');
  }

  private function set_encrypted_key(string $enc = '') {
    update_option('nostrtium-enc-privkey', $enc);
  }

  private function get_encrypted_key() {
    return get_option('nostrtium-enc-privkey');
  }

  public function check_user() {
    if (!current_user_can(apply_filters('nostrtium_role', PJV_NOSTRTIUM_DEFAULT_USER_ROLE))) {
      wp_die('Invalid permissions');
    }
  }

  private function sanitize_ws_url(string $url = null) {
    return sanitize_url($url, ['ws', 'wss']);
  }

  public function save_relays() {
    check_ajax_referer('nostrtium-ajax-nonce', 'security');
    $this->check_user();

    $relays = array_unique(array_map([$this, 'sanitize_ws_url'], $_POST['relays'])) ?? null;
    if ($relays == null) {
      wp_send_json_error('No relays have been defined.');
    }

    $this->set_relays($relays);
    wp_send_json_success();
  }
}
Nostrtium_Settings::get_instance();