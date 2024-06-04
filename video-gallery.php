<?php
/**
 * Plugin Name: Custom Video Gallery
 * Description: Video Gallery with advanced category filters that handles youtube url to render on lightbox, use this shortcode to display the gallery [video_gallery]
 * Version:     1.0.0
 * Author:      Julius Enriquez
 * Text Domain: custom-video-gallery
 */

function create_video_post_type() {
    register_post_type('video', [
        'labels' => [
            'name' => __('Videos'),
            'singular_name' => __('Video')
        ],
        'public' => true,
        'has_archive' => true,
        'supports' => ['title', 'thumbnail'],
        'taxonomies' => ['video_category'],
    ]);

    register_taxonomy('video_category', 'video', [
        'labels' => [
            'name' => __('Video Categories'),
            'singular_name' => __('Video Category')
        ],
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => ['slug' => 'video-category'],
    ]);
}
add_action('init', 'create_video_post_type');


function add_video_meta_box() {
    add_meta_box('video_meta_box', 'Video URL', 'video_meta_box_callback', 'video', 'normal', 'high');
}
add_action('add_meta_boxes', 'add_video_meta_box');

function video_meta_box_callback($post) {
    wp_nonce_field('save_video_meta_box_data', 'video_meta_box_nonce');
    $value = get_post_meta($post->ID, '_video_url', true);
    echo '<label for="video_url">Video URL</label>';
    echo '<input type="text" id="video_url" name="video_url" value="' . esc_attr($value) . '" size="25" />';
}

function save_video_meta_box_data($post_id) {
    if (!isset($_POST['video_meta_box_nonce']) || !wp_verify_nonce($_POST['video_meta_box_nonce'], 'save_video_meta_box_data')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    if (!isset($_POST['video_url'])) {
        return;
    }
    $video_url = sanitize_text_field($_POST['video_url']);
    update_post_meta($post_id, '_video_url', $video_url);
}
add_action('save_post', 'save_video_meta_box_data');

function video_gallery_shortcode() {
    // Get all video categories
    $categories = get_terms([
        'taxonomy' => 'video_category',
        'hide_empty' => false,
    ]);

    ob_start();

    // Display the category filter buttons
    echo '<div id="video-category-filter">';
    echo '<button id="all-videos" class="filter-category active" data-category="all">All Videos</button>';
    if ($categories && !is_wp_error($categories)) {
        foreach ($categories as $category) {
            echo '<button class="filter-category" data-category="' . esc_attr($category->slug) . '">' . esc_html($category->name) . '</button>';
        }
    }
    echo '</div>';

    // Display the video gallery
    $args = [
        'post_type' => 'video',
        'posts_per_page' => -1,
    ];
    $videos = new WP_Query($args);
    if ($videos->have_posts()) {
        echo '<div id="video-gallery">';
        while ($videos->have_posts()) {
            $videos->the_post();
            $video_url = get_post_meta(get_the_ID(), '_video_url', true);
            $thumbnail = get_the_post_thumbnail(get_the_ID(), 'medium');
            $categories = get_the_terms(get_the_ID(), 'video_category');
            $cat_classes = '';
            if ($categories && !is_wp_error($categories)) {
                foreach ($categories as $category) {
                    $cat_classes .= ' category-' . $category->slug;
                }
            }
            echo '<div class="video-item' . esc_attr($cat_classes) . '">';
            echo '<a href="' . esc_url($video_url) . '" class="video-link" data-mfp-type="iframe">';
            echo '<div class="thumbnail-wrap">';
            echo $thumbnail;
            echo '<div class="post-title">' . get_the_title() . '</div>';
            echo '</div>';
            echo '</a>';
            echo '</div>';
        }
        echo '</div>';
    }
    wp_reset_postdata();


    return ob_get_clean();
}
add_shortcode('video_gallery', 'video_gallery_shortcode');


function enqueue_video_gallery_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('magnific-popup', plugins_url('js/jquery.magnific-popup.js', __FILE__), array('jquery'), '1.1.0', true);
    wp_enqueue_style('magnific-popup-css', plugins_url('css/magnific-popup.css', __FILE__), array(), '1.1.0');
    wp_enqueue_script('video-gallery-script', plugins_url('/js/video-gallery.js', __FILE__), ['jquery'], null, true);
    wp_enqueue_style('video-gallery-style', plugins_url('/css/video-gallery.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'enqueue_video_gallery_scripts');
