<?php

/**
 * Plugin Name:      Nostrtium
 * Plugin URI:       https://github.com/pjv/nostrtium
 * Description:      Post to Nostr from WordPress
 * Author:           pjv
 * Author URI:       https://github.com/pjv
 * Text Domain:      nostrtium
 * Domain Path:      /languages
 * Version:          0.7.4
 * Requires at least 6.0
 * Requires PHP      8.1
 * License           Unlicense
 * License URI       https://unlicense.org
 * 
 * @package          Nostrtium
 */

if (!defined('WPINC')) {
  exit;
}

define('PJV_NOSTRTIUM_VERSION', '0.7.4');
define('PJV_NOSTRTIUM_DIR', plugin_dir_path(__FILE__));
define('PJV_NOSTRTIUM_DEFAULT_USER_ROLE', 'edit_posts');
define('PJV_NOSTRTIUM_STORAGE', wp_upload_dir()['basedir'] . '/nostrtium_' . md5(LOGGED_IN_SALT) . '/');

require_once PJV_NOSTRTIUM_DIR . 'classes/class-nostrtium-requirements-check.php';
$pjv_nostrtium_requirements_check = new Nostrtium_Requirements_Check(['title'   => 'Nostrtium',
  'php'        => '8.1',
  'extensions' => ['gmp', 'bcmath'],
  'wp'         => '6.0',
  'dir'        => wp_upload_dir()['basedir'],
  'file'       => __FILE__,
]);
if ($pjv_nostrtium_requirements_check->passes()) {
  require_once PJV_NOSTRTIUM_DIR . 'vendor/autoload.php';
  require_once PJV_NOSTRTIUM_DIR . 'classes/class-nostrtium.php';
  $pjv_nostrtium_plugin = Nostrtium::get_instance();

  register_activation_hook(__FILE__, [$pjv_nostrtium_plugin, 'activate']);
  register_deactivation_hook(__FILE__, [$pjv_nostrtium_plugin, 'deactivate']);
}
unset($pjv_nostrtium_requirements_check);