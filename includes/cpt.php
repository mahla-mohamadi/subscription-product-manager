<?php
// Register Custom Post Type: sproduct
function sproduct_custom_post_type() {
    register_post_type('sproduct', [
        'labels' => [
            'name' => 'محصولات اشتراکی',
            'singular_name' => 'محصول اشتراکی',
        ],
        'public' => true,
        'menu_icon' => 'dashicons-cart',
        'supports' => ['title'],
        'show_in_rest' => true,
    ]);
    flush_rewrite_rules();
}
add_action('init', 'sproduct_custom_post_type');