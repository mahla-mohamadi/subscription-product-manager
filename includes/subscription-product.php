<?php

// افزودن متاباکس به محصولات ووکامرس
function woocommerce_add_meta_box()
{
    add_meta_box(
        'woocommerce_meta_box_id',
        'لیست اشتراک ها',
        'woocommerce_meta_box_callback',
        'product',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'woocommerce_add_meta_box');

// محتوای متاباکس
function woocommerce_meta_box_callback($post)
{
    // Nonce برای امنیت
    wp_nonce_field('woocommerce_meta_box_nonce_action', 'woocommerce_meta_box_nonce');

    // بازیابی اشتراک‌های انتخاب‌شده
    $selected_subscriptions = get_post_meta($post->ID, '_selected_sproduct_ids', true);
    $selected_subscriptions = is_array($selected_subscriptions) ? $selected_subscriptions : [];
    // نمایش ID محصول ووکامرس
    echo '<h4>اطلاعات محصول:</h4>';
    echo '<div>شناسه محصول: <strong>' . $post->ID . '</strong></div>';

    echo '<h4>لیست اشتراک‌های موجود:</h4>';

    // کوئری برای دریافت لیست sproductها
    $args = [
        'post_type' => 'sproduct',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    ];

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        echo '<div style="max-height: 300px; overflow-y: auto; border: 1px solid #ccc; padding: 10px;">';
        while ($query->have_posts()) {
            $query->the_post();

            $checked = in_array(get_the_ID(), $selected_subscriptions) ? 'checked' : '';

            echo '<label style="display: block; margin-bottom: 5px;">';
            echo '<input type="checkbox" name="sproduct_ids[]" value="' . get_the_ID() . '" ' . $checked . ' /> ';
            echo 'شناسه: <strong>' . get_the_ID() . '</strong> - ' . get_the_title();
            echo '</label>';
        }
        echo '</div>';
    } else {
        echo '<p>هیچ اشتراکی یافت نشد.</p>';
    }

    wp_reset_postdata();

}

// ذخیره اطلاعات متاباکس
add_action('save_post', 'woocommerce_save_meta_box_data');
function woocommerce_save_meta_box_data($post_id)
{
    // بررسی nonce برای امنیت
    if (
        !isset($_POST['woocommerce_meta_box_nonce']) ||
        !wp_verify_nonce($_POST['woocommerce_meta_box_nonce'], 'woocommerce_meta_box_nonce_action')
    ) {
        return;
    }

    // بررسی مجوزهای کاربر
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // ذخیره اشتراک‌های انتخاب‌شده
    if (isset($_POST['sproduct_ids']) && is_array($_POST['sproduct_ids'])) {
        $selected_subscriptions = array_map('intval', $_POST['sproduct_ids']);
        update_post_meta($post_id, '_selected_sproduct_ids', $selected_subscriptions);
    } else {
        delete_post_meta($post_id, '_selected_sproduct_ids');
    }
}

function add_subscription_message_to_product_title() {
    global $product;

    if (!$product) return;

    // Get the custom field value
    $selected_sproduct_ids = get_post_meta($product->get_id(), '_selected_sproduct_ids', true);

    // Check if it has a value
    if (!empty($selected_sproduct_ids)) {
        echo '<p>این محصول مخصوص اشتراک‌های زیر است</p>';
        foreach($selected_sproduct_ids as $sid){
            echo '<a href="'.get_the_permalink($sid).'">'.get_the_title($sid).'<a>';
            echo '<br>';
        }

    }
}
add_action('woocommerce_single_product_summary', 'add_subscription_message_to_product_title', 6);

