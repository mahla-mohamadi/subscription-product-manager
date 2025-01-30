<?php
// Add virtual product to cart with custom data
function add_custom_virtual_product_to_cart($spn, $pn, $price , $duration , $request , $form) {
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
        'spn'  => $spn,
        'pn' => $pn,
        'price' => $price,
        'duration' => $duration,
        'request' => $request,
        'form' => $form
    );

    // Add the product to the cart
    return WC()->cart->add_to_cart($product_id, 1, 0, array(), $cart_item_data);
}


// Show custom data in cart item
add_filter('woocommerce_get_item_data', 'display_custom_cart_item_data', 10, 2);
function display_custom_cart_item_data($item_data, $cart_item) {
    if (isset($cart_item['spn'])) {
        $item_data[] = array(
            'key'   => __('اشتراک', 'woocommerce'),
            'value' => wc_clean($cart_item['spn'])
        );
    }
    if (isset($cart_item['pn'])) {
        $item_data[] = array(
            'key'   => __('پلن', 'woocommerce'),
            'value' => wc_clean($cart_item['pn'])
        );
    }
    if (isset($cart_item['duration'])) {
        $item_data[] = array(
            'key'   => __('مدت زمان', 'woocommerce'),
            'value' => wc_clean($cart_item['duration'])
        );
    }
    if (isset($cart_item['request'])) {
        $item_data[] = array(
            'key'   => __('اقدام', 'woocommerce'),
            'value' => wc_clean($cart_item['request'])
        );
    }
    return $item_data;
}

// Save custom data to order items
add_action('woocommerce_checkout_create_order_line_item', 'save_custom_order_item_meta', 10, 4);
function save_custom_order_item_meta($item, $cart_item_key, $values, $order) {
    if (isset($values['spn'])) {
        $item->add_meta_data(__('اشتراک', 'woocommerce'), $values['spn'], true);
    }
    if (isset($values['pn'])) {
        $item->add_meta_data(__('پلن', 'woocommerce'), $values['pn'], true);
    }
    // if (isset($values['price'])) {
    //     $item->add_meta_data(__('قیمت', 'woocommerce'), wc_price($values['price']), true);
    // }
    if (isset($values['duration'])) {
        $item->add_meta_data(__('مدت زمان', 'woocommerce'), $values['duration'], true);
    }
    if (isset($values['request'])) {
        $item->add_meta_data(__('اقدام', 'woocommerce'), $values['request'], true);
    }
    if (isset($values['form'])) {
        $item->add_meta_data(__('_sproduct_form', 'woocommerce'), $values['form'], true);
    }
    if (isset($values['price'])) {
        $item->add_meta_data(__('_sproduct_paid', 'woocommerce'), $values['price'], true);
    }
}

// Adjust price in cart
add_action('woocommerce_before_calculate_totals', 'set_custom_cart_item_price', 20, 1);
function set_custom_cart_item_price($cart) {
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }

    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        if (isset($cart_item['price'])) {
            $cart_item['data']->set_price($cart_item['price']);
        }
    }
}

// Example usage:
// add_custom_virtual_product_to_cart('Custom Product Name', 29.99, 'mah');


// Hook into WooCommerce order status change to 'wc-processing'
add_action('woocommerce_order_status_processing', 'sproduct_create_subscription_on_order', 10, 1);
function sproduct_create_subscription_on_order($order_id) {
    global $wpdb;
    $order = wc_get_order($order_id);
    if (!$order) return;
    foreach ($order->get_items() as $item_id => $item) {
        $spn = wc_get_order_item_meta($item_id, 'اشتراک', true);  // Subscription Product Name
        $pn = wc_get_order_item_meta($item_id, 'پلن', true);    // Plan Name
        // $price = wc_get_order_item_meta($item_id, 'قیمت', true);  // Price
        $price = $item->get_total();
        $duration = wc_get_order_item_meta($item_id, 'مدت زمان', true);  // Duration in days
        $form = wc_get_order_item_meta($item_id, '_sproduct_form', true);
        if (empty($spn) || empty($pn) || empty($price) || empty($duration)) {
            error_log("Missing subscription item meta for order #{$order_id}, item #{$item_id}");
            continue;
        }
        $start_date = current_time('Y-m-d');
        $end_date = date('Y-m-d', strtotime("+{$duration} days"));
        $wpdb->insert(
            "{$wpdb->prefix}s_subscriptions",
            array(
                'sproduct_id' => $item->get_product_id(),
                'sproduct_name' => $spn,
                'user_id'     => $order->get_user_id(),
                'start_date'  => $start_date,
                'end_date'    => $end_date,
                'plan'        => $pn,
                'formdata'        => $form,
                'amount'      => $price,
                'status'      => 'pending',
            ),
            array('%d', '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%s')
        );
        $subscription_id = $wpdb->insert_id;
        if ($subscription_id) {
            wc_add_order_item_meta($item_id, '_subscription_id', $subscription_id);
        } else {
            error_log("Failed to create subscription for order #{$order_id}, item #{$item_id}");
        }
    }
}








// Handle Form Submission via AJAX
add_action('wp_ajax_sproduct_submit_form', 'sproduct_submit_form');
add_action('wp_ajax_nopriv_sproduct_submit_form', 'sproduct_submit_form');
function sproduct_submit_form() {
    if (!check_ajax_referer('sproduct_form_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => 'Invalid nonce']);
        return;
    }


    $planName  = isset($_POST['planName']) ? sanitize_text_field($_POST['planName']) : '';
    $postID  = isset($_POST['postID']) ? sanitize_text_field($_POST['postID']) : '';
    $productName = get_the_title($postID);
    $planPrice  = isset($_POST['planPrice']) ? sanitize_text_field($_POST['planPrice']) : '';
    $planDuration  = isset($_POST['planDuration']) ? sanitize_text_field($_POST['planDuration']) : '';
    $requestType  = isset($_POST['requestType']) ? sanitize_text_field($_POST['requestType']) : '';
    $submittedFormData  = isset($_POST['submittedFormData']) ? json_decode(stripslashes($_POST['submittedFormData']), true) : [];

    error_log('Form data Before Update: ' . print_r($submittedFormData, true));

    // Handle file upload
    if (!empty($_FILES)) {
        $uploaded_files = [];
        foreach ($_FILES as $file_key => $file) {
            if ($file['error'] === UPLOAD_ERR_OK) {
                $uploaded_file = wp_handle_upload($file, ['test_form' => false]);
                if (isset($uploaded_file['url'])) {
                    $uploaded_files[$file_key] = $uploaded_file['url'];
                } else {
                    error_log('File upload failed: ' . print_r($uploaded_file, true));
                }
            } else {
                error_log("File upload error for key $file_key: " . $file['error']);
            }
        }
        error_log('Upload File Array is:'.print_r($uploaded_files , true));

        foreach ($uploaded_files as $key => $url) {
            $submittedFormData[$key] = $url;
        }
        error_log('Updated From is:'.print_r($submittedFormData , true));
    }

    error_log('Updated form data with files: ' . print_r($submittedFormData, true));

    // Add product to cart
    $cart_item_key = add_custom_virtual_product_to_cart(
        $productName,
        $planName,
        $planPrice,
        $planDuration,
        $requestType,
        json_encode($submittedFormData, JSON_UNESCAPED_UNICODE)
    );

    if ($cart_item_key) {
        error_log('Product added to cart: ' . $cart_item_key);
        wp_send_json_success(['added' => 1]);
    } else {
        error_log('Failed to add product to cart.');
        wp_send_json_error(['added' => 0]);
    }
}
