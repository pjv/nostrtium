<?php
/**
 * Plugin Name:     WP Nostr
 * Plugin URI:      https://github.com/pjv/wp-nostr
 * Description:     Post to Nostr from WordPress
 * Author:          pjv
 * Author URI:      https://github.com/pjv
 * Text Domain:     wp-nostr
 * Domain Path:     /languages
 * Version:         0.5.0
 *
 * @package         WP_Nostr
 */

/*
This is free and unencumbered software released into the public domain.

Anyone is free to copy, modify, publish, use, compile, sell, or
distribute this software, either in source code form or as a compiled
binary, for any purpose, commercial or non-commercial, and by any
means.

In jurisdictions that recognize copyright laws, the author or authors
of this software dedicate any and all copyright interest in the
software to the public domain. We make this dedication for the benefit
of the public at large and to the detriment of our heirs and
successors. We intend this dedication to be an overt act of
relinquishment in perpetuity of all present and future rights to this
software under copyright law.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS BE LIABLE FOR ANY CLAIM, DAMAGES OR
OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.

For more information, please refer to <https://unlicense.org>
*/

if (!defined('WPINC')) {
  exit;
}

define('PJV_WPNOSTR_VERSION', '0.5.0');
define('PJV_WPNOSTR_DIR', plugin_dir_path(__FILE__));

require_once PJV_WPNOSTR_DIR . 'classes/class-wp-nostr-requirements-check.php';
$PJV_WPNostr_requirements_check = new WP_Nostr_Requirements_Check([
  'title' => 'WP Nostr',
  'php' => '8.1',
  'wp' => '6.0',
  'dir' => PJV_WPNOSTR_DIR,
  'file' => __FILE__,
]);
if ($PJV_WPNostr_requirements_check->passes()) {
  require_once PJV_WPNOSTR_DIR . '/vendor/autoload.php';
  require_once PJV_WPNOSTR_DIR . 'classes/class-wp-nostr.php';
  $plugin = WP_Nostr::get_instance();

  register_activation_hook(__FILE__, [$plugin, 'activate']);
  register_deactivation_hook(__FILE__, [$plugin, 'deactivate']);
}
unset($PJV_WPNostr_requirements_check);