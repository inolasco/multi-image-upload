<?php

/*
  Plugin Name: More Post Images
  Plugin URI: https://github.com/inolasco/multi-image-upload
  Description: This plugin adds a meta box to upload multiple images for posts and pages.
  Author: Ivan Nolasco
  Version: 1.0
  Author URI: http://ivannolasco.com
 */

/*

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Calls the class on the post add/edit screens.
 */
function call_Multi_Image_Uploader() {
    new Multi_Image_Uploader();
}


/**
 * Get images attached to some post
 *
 * @param int $post_id
 * @return array
 */
function miu_get_images($post_id) {
    $value = get_post_meta($post_id, 'miu_images', true);
    // $images = unserialize($value);
    // $result = array();

    // if (!empty($images)) {
    //     foreach ($images as $image) {
    //         $result[] = $image;
    //     }
    // }
    return $value;
}

/**
 * Multi_Image_Uploader
 */
class Multi_Image_Uploader {

    var $post_types = array();

    /**
     * Initialize Multi_Image_Uploader
     */
    public function __construct() {
        //limit meta box to certain post types
        $this->post_types = array('post', 'page', 'hf_drink_detail', 'foodmenu');
        add_action('add_meta_boxes', array($this, 'add_meta_box'));
        add_action('save_post', array($this, 'save'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    // Adds the meta box container.
    public function add_meta_box($post_type) {

        if (in_array($post_type, $this->post_types)) {
            add_meta_box(
                    'multi_image_upload_meta_box',
                    __('Additional images', 'miu_textdomain'),
                    array($this, 'render_meta_box_content'),
                    $post_type,
                    'side', // normal, advanced, side
                    'low' // 'high', 'core', 'default' or 'low'
            );
        }
    }

    /**
     * Save the images when the post is saved.
     * @param int $post_id The ID of the post being saved.
     */
    public function save($post_id) {
        /*
         * We need to verify this came from the our screen and with proper authorization,
         * because save_post can be triggered at other times.
         */

        // Check if our nonce is set.
        if (!isset($_POST['miu_inner_custom_box_nonce'])) {
            return $post_id;
        }

        $nonce = $_POST['miu_inner_custom_box_nonce'];

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($nonce, 'miu_inner_custom_box')) {
            return $post_id;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }

        // Check the user's permissions.
        if ('page' == $_POST['post_type']) {
            if (!current_user_can('edit_page', $post_id)) {
                return $post_id;
            }
        } else {
            if (!current_user_can('edit_post', $post_id)) {
                return $post_id;
            }
        }

        /* OK, its safe for us to save the data now. */

        // Validate user input.
        $posted_images = $_POST['miu_images'];

        $miu_images = array();

        // If a single image was received
        // TODO: Validate what's being sent
        if (!is_array($posted_images) && $posted_images->id) {
            array_push($miu_images, json_decode(urldecode($posted_images)) );
        } else if (is_array($posted_images)) {
            foreach ($posted_images as $image_url) {
                $image_url = json_decode(urldecode($image_url));
                if (!empty($image_url) && $image_url->id) {
                    $miu_images[] = $image_url;
                }
            }
        }

        if (count($miu_images) > 0) {
            // Update the miu_images meta field.
            update_post_meta($post_id, 'miu_images', $miu_images);
        } else {
            delete_post_meta($post_id, 'miu_images');
        }

    }

    /**
     * Render Meta Box content.
     *
     * @param WP_Post $post The post object.
     */
    public function render_meta_box_content($post) {

        // Add an nonce field so we can check for it later.
        wp_nonce_field('miu_inner_custom_box', 'miu_inner_custom_box_nonce');

        // Use get_post_meta to retrieve an existing value from the database.
        $value = get_post_meta($post->ID, 'miu_images', true);
        // echo '<pre>'. $value .'</pre>';

        echo '<div id="miu_images">';

        if ($value && is_array($value)) {
            foreach ($value as $image) {
                // $script.="addRow('{$image}');";
                if ($image && $image->id) {
                    echo '<div id="row-miu-'.$image->id.'" style=\'margin-bottom: 20px;\'><img width="60" height="60" src="'. $image->thumbnail .'" class="attachment-post-thumbnail" alt="'.$image->filename.'" style=\'vertical-align: top;\' /><h4 style=\'display: inline-block;width: 180px;margin-left: 10px;\'>'.$image->title.'</h4><a class="miu_remove_image_button" id="miu_row-'. $image->id .'">Remove</a><input type="hidden" name="miu_images[]" value="'. urlencode(json_encode($image)) .'" /></div>';
                }
            }
        }

        echo '</div><input id="miu_upload_image_button" type="button" value="Add Image" class="button" />';
    }

    function enqueue_scripts($hook) {
        if ('post.php' != $hook && 'post-edit.php' != $hook && 'post-new.php' != $hook) {
            return;
        }
        wp_enqueue_script('miu_script', plugin_dir_url(__FILE__) . 'miu_script.js', array('jquery'), null, true);
    }
}

if (is_admin()) {
    add_action('load-post.php', 'call_Multi_Image_Uploader');
    add_action('load-post-new.php', 'call_Multi_Image_Uploader');
}