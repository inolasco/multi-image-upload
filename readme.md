### Multi Image Upload
Requires at least: 3.9
Tested up to: 3.9.1
Stable tag: 1.0
License: GPLv2 or later

This plugin adds a meta box to upload multiple images for posts and pages.

#### Description

This plugin adds a meta box to upload multiple images for posts and pages. You can enable it for custom post types also, please see installation instructions.

Use this plugin if you want to quickly add a feature to upload multiple images for a page, post or custom post type.

Forked from [multi-image-upload](https://github.com/wp-plugins/multi-image-upload) by [tahiryasin](http://about.me/tahiryasin)

#### Installation

1. Upload plugin to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. To retrieve linked images [Use miu_get_images()](http://wordpress.org/extend/plugins/multi-image-upload/other_notes/#miu_get_images()) into the loop to get an array of image URLs
4. Optional

If you need to enable this meta box for your custom post type for example 'book'. Just edit the multi-image-upload.php as shown below

Replace: 
`$this->post_types = array('post', 'page');`
With:
`$this->post_types = array('post', 'page', 'book');`


    miu_get_images();

This function can be called from any template file to get attached images for the page/post being viewed.
It returns an array of object with data for the attached image URL.

It take only one argument, **post_id** (integer) to get images linked to a specific post.

