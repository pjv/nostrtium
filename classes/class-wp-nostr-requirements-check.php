<?php

if (!defined('ABSPATH')) {
  exit;
}

class WP_Nostr_Requirements_Check {
  // args
  private $title = '';
  private $php = '8.1';
  private $wp = '6.0';
  private $dir = '';
  private $file;
  // end args

  public function __construct($args) {
    foreach ($args as $key => $setting) {
      if (isset($setting)) {
        $this->$key = $setting;
      }
    }
  }
  public function passes() {
    $passes = $this->php_passes() && $this->wp_passes() && $this->dir_passes();
    if (!$passes) {
      add_action('admin_notices', [$this, 'deactivate']);
    }

    return $passes;
  }
  public function deactivate() {
    if (isset($this->file)) {
      deactivate_plugins(plugin_basename($this->file));
    }
  }
  private function php_passes() {
    if ($this->__php_at_least($this->php)) {
      return true;
    } else {
      add_action('admin_notices', [$this, 'php_version_notice']);

      return false;
    }
  }
  private static function __php_at_least($min_version) {
    return version_compare(phpversion(), $min_version, '>=');
  }
  public function php_version_notice() {
    echo '<div class="error">';
    echo '<p>The &#8220;' . esc_html($this->title) . '&#8221; plugin cannot run on PHP versions older than ' . $this->php . '. Please contact your host and ask them to upgrade.</p>';
    echo '</div>';
  }
  private function wp_passes() {
    if ($this->__wp_at_least($this->wp)) {
      return true;
    } else {
      add_action('admin_notices', [$this, 'wp_version_notice']);

      return false;
    }
  }
  private static function __wp_at_least($min_version) {
    return version_compare(get_bloginfo('version'), $min_version, '>=');
  }
  public function wp_version_notice() {
    echo '<div class="error">';
    echo '<p>The &#8220;' . esc_html($this->title) . '&#8221; plugin cannot run on WordPress versions older than ' . $this->wp . '. Please update WordPress.</p>';
    echo '</div>';
  }
    private function dir_passes() {
      if (is_writable($this->dir)) {
        return true;
      } else {
        add_action('admin_notices', [$this, 'dir_writeable_notice']);

        return false;
      }
    }
  public function dir_writeable_notice() {
    echo '<div class="error">';
    echo '<p>The &#8220;' . esc_html($this->title) . '&#8221; plugin cannot run without its own directory being writeable.</p>';
    echo '</div>';
  }
}
