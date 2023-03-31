<?php

if (!defined('WPINC')) {
  exit;
}

add_action('add_meta_boxes', ['PJV_Nostrtium_Meta_Box', 'add']);

abstract class PJV_Nostrtium_Meta_Box {
  /**
   * Set up and add the meta box.
   */
  public static function add() {
    if (current_user_can(apply_filters('nostrtium_role', PJV_NOSTRTIUM_DEFAULT_USER_ROLE))) {
      $screens = ['post'];
      foreach ($screens as $screen) {
        add_meta_box(
          'pjv_nostrtium_box',
          'Post to Nostr',
          [self::class, 'box_html'],
          $screen
        );
      }
    }
  }

  /**
   * Display the meta box HTML to the user.
   *
   * @param WP_Post $post   Post object.
   */
  public static function box_html($post) {
    include PJV_NOSTRTIUM_DIR . 'views/metabox.php';
  }
}
