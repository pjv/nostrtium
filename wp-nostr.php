<?php

/**
 * Plugin Name:      WP Nostr
 * Plugin URI:       https://github.com/pjv/wp-nostr
 * Description:      Post to Nostr from WordPress
 * Author:           pjv
 * Author URI:       https://github.com/pjv
 * Text Domain:      wp-nostr
 * Domain Path:      /languages
 * Version:          0.5.3
 * Requires at least 6.0
 * Requires PHP      8.1
 * License           Unlicense
 * License URI       https://unlicense.org
 * 
 * @package          WP_Nostr
 */

if (!defined('WPINC')) {
  exit;
}

define('PJV_WPNOSTR_VERSION', '0.5.3');
define('PJV_WPNOSTR_DIR', plugin_dir_path(__FILE__));
define('PJV_WPNOSTR_DEFAULT_USER_ROLE', 'edit_posts');

require_once PJV_WPNOSTR_DIR . 'classes/class-wp-nostr-requirements-check.php';
$PJV_WPNostr_requirements_check = new WP_Nostr_Requirements_Check([
  'title' => 'WP Nostr',
  'php'   => '8.1',
  'wp'    => '6.0',
  'dir'   => PJV_WPNOSTR_DIR,
  'file'  => __FILE__,
]);
if ($PJV_WPNostr_requirements_check->passes()) {
  require_once PJV_WPNOSTR_DIR . '/vendor/autoload.php';
  require_once PJV_WPNOSTR_DIR . 'classes/class-wp-nostr.php';
  $plugin = WP_Nostr::get_instance();

  register_activation_hook(__FILE__, [$plugin, 'activate']);
  register_deactivation_hook(__FILE__, [$plugin, 'deactivate']);
}
unset($PJV_WPNostr_requirements_check);