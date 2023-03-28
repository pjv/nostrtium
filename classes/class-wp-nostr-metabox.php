<?php

if (!defined('WPINC')) {
  exit;
}

add_action('add_meta_boxes', ['PJV_WPNostr_Meta_Box', 'add']);
// add_action('save_post', ['WPOrg_Meta_Box', 'save']);

abstract class PJV_WPNostr_Meta_Box {
  /**
   * Set up and add the meta box.
   */
  public static function add() {
    $screens = ['post'];
    foreach ($screens as $screen) {
      add_meta_box(
        'pjv_wpnostr_box',
        'Post to Nostr',
        [self::class, 'box_html'],
        $screen
      );
    }
  }

  /**
   * Display the meta box HTML to the user.
   *
   * @param WP_Post $post   Post object.
   */
  public static function box_html($post) {
    include PJV_WPNOSTR_DIR . 'views/metabox.php';
  }




  /**
   * Save the meta box selections.
   *
   * @param int $post_id  The post ID.
   */
  // public static function save(int $post_id) {
  //   if (array_key_exists('wporg_field', $_POST)) {
  //     update_post_meta(
  //       $post_id,
  //       '_wporg_meta_key',
  //       $_POST['wporg_field']
  //     );
  //   }
  // }
}