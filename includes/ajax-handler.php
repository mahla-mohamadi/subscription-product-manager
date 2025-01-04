<?php
// Add virtual product to cart with custom data
function add_custom_virtual_product_to_cart($product_name, $product_price, $stype = 'mah') {
    $product_sku = 's_prod_virtual';
    $product_id = wc_get_product_id_by_sku($product_sku);

    // Check if the product exists
    if (!$product_id) {
        wc_add_notice('محصول اشتراک یافت نشد', 'error');
        return;
    }

    // Remove existing product with the same SKU from the cart
    $cart = WC()->cart->get_cart();
    foreach ($cart as $cart_item_key => $cart_item) {
        $product = $cart_item['data'];
        if ($product->get_sku() === $product_sku) {
            WC()->cart->remove_cart_item($cart_item_key);
        }
    }

    // Prepare custom cart item data
    $cart_item_data = array(
        'custom_name'  => $product_name,
        'custom_price' => $product_price,
        'stype'        => $stype
    );

    // Add the product to the cart
    WC()->cart->add_to_cart($product_id, 1, 0, array(), $cart_item_data);
}


// Show custom data in cart item
add_filter('woocommerce_get_item_data', 'display_custom_cart_item_data', 10, 2);
function display_custom_cart_item_data($item_data, $cart_item) {
    if (isset($cart_item['custom_name'])) {
        $item_data[] = array(
            'key'   => __('Custom Name', 'woocommerce'),
            'value' => wc_clean($cart_item['custom_name'])
        );
    }
    if (isset($cart_item['stype'])) {
        $item_data[] = array(
            'key'   => __('Type', 'woocommerce'),
            'value' => wc_clean($cart_item['stype'])
        );
    }
    return $item_data;
}

// Save custom data to order items
add_action('woocommerce_checkout_create_order_line_item', 'save_custom_order_item_meta', 10, 4);
function save_custom_order_item_meta($item, $cart_item_key, $values, $order) {
    if (isset($values['custom_name'])) {
        $item->add_meta_data(__('Custom Name', 'woocommerce'), $values['custom_name'], true);
    }
    if (isset($values['custom_price'])) {
        $item->add_meta_data(__('Custom Price', 'woocommerce'), wc_price($values['custom_price']), true);
    }
    if (isset($values['stype'])) {
        $item->add_meta_data(__('Type', 'woocommerce'), $values['stype'], true);
    }
}

// Adjust price in cart
add_action('woocommerce_before_calculate_totals', 'set_custom_cart_item_price', 20, 1);
function set_custom_cart_item_price($cart) {
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }

    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        if (isset($cart_item['custom_price'])) {
            $cart_item['data']->set_price($cart_item['custom_price']);
        }
    }
}

// Example usage:
// add_custom_virtual_product_to_cart('Custom Product Name', 29.99, 'mah');






// Handle Form Submission via AJAX
add_action('wp_ajax_sproduct_submit_form', 'sproduct_submit_form');
add_action('wp_ajax_nopriv_sproduct_submit_form', 'sproduct_submit_form');
function sproduct_submit_form() {
    check_ajax_referer('sproduct_form_nonce', 'nonce');
    add_custom_virtual_product_to_cart('Servicing the Heater', 40);
}