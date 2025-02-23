<?php

function enqueue_wallet_scripts() {
    wp_enqueue_script(
        'wallet-js',
        SPRODUCT_URL . 'assets/wallet.js',
        ['jquery'],
        null,
        true
    );
    wp_enqueue_style('wallet-css', SPRODUCT_URL . 'assets/wallet.css');

    // Localize script to pass the AJAX URL
    wp_localize_script('wallet-js', 'wallet_vars', [
        'ajax_url' => admin_url('admin-ajax.php'),
    ]);
}
add_action('wp_enqueue_scripts', 'enqueue_wallet_scripts');


function create_hidden_wallet_virtual_product(){
    if (class_exists('WC_Product')) {
        if (!wc_get_product_id_by_sku('wallet_prod_virtual')) {
            $product = new WC_Product();
            $product->set_name('افزایش موجودی کیف پول');
            $product->set_status('publish');
            $product->set_catalog_visibility('hidden');
            $product->set_virtual(true);
            $product->set_price(1);
            $product->set_regular_price(1);
            $product->set_sku('wallet_prod_virtual');
            $product->save();
        }
    }
}
add_action('admin_init', 'create_hidden_wallet_virtual_product');

// Add virtual product to cart with custom data
function add_custom_wallet_virtual_product_to_cart($price) {
    $product_sku = 'wallet_prod_virtual';
    $product_id = wc_get_product_id_by_sku($product_sku);

    // Check if the product exists
    if (!$product_id) {
        wc_add_notice('کیف پول یافت نشد', 'error');
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
        'price' => $price,
    );

    // Add the product to the cart
    return WC()->cart->add_to_cart($product_id, 1, 0, array(), $cart_item_data);
}



function process_top_up_wallet() {
    // Check nonce for security
    if (!isset($_POST['top_up_wallet_nonce_field']) || !wp_verify_nonce($_POST['top_up_wallet_nonce_field'], 'top_up_wallet_nonce')) {
        wc_add_notice(__('Invalid request. Please try again.', 'woocommerce'), 'error');
        wp_safe_redirect(wc_get_account_endpoint_url('wallet'));
        exit;
    }

    $user_id = get_current_user_id();
    $top_up_amount = isset($_POST['top_up_amount']) ? floatval($_POST['top_up_amount']) : 0;

    if ($top_up_amount <= 0) {
        wc_add_notice(__('Please enter a valid amount.', 'woocommerce'), 'error');
        wp_safe_redirect(wc_get_account_endpoint_url('wallet'));
        exit;
    }

    $product_sku = 'wallet_prod_virtual';
    $product_id = wc_get_product_id_by_sku($product_sku);

    if (!$product_id) {
        wc_add_notice(__('The wallet top-up product is unavailable.', 'woocommerce'), 'error');
        wp_safe_redirect(wc_get_account_endpoint_url('wallet'));
        exit;
    }

    // Ensure WooCommerce cart is initialized
    if (null === WC()->cart) {
        wc_load_cart();
    }

    // Add virtual product to cart
    WC()->cart->empty_cart(); // Optional: Remove other items if you only want this product in the cart.
    $cart_item_key = WC()->cart->add_to_cart($product_id, 1, 0, array(), array('price' => $top_up_amount));

    if ($cart_item_key) {
        wc_add_notice(__('The wallet top-up product has been added to your cart. Please proceed to checkout.', 'woocommerce'), 'success');
        wp_safe_redirect(wc_get_cart_url());
    } else {
        wc_add_notice(__('Failed to add the wallet top-up product. Please try again.', 'woocommerce'), 'error');
        wp_safe_redirect(wc_get_account_endpoint_url('wallet'));
    }
    exit;
}
add_action('admin_post_top_up_wallet', 'process_top_up_wallet');
add_action('admin_post_nopriv_top_up_wallet', 'process_top_up_wallet');


// Add wallet balance to user profile
function add_wallet_balance_to_user($user_id) {
    if (!get_user_meta($user_id, 'wallet_balance', true)) {
        update_user_meta($user_id, 'wallet_balance', 0);
    }
}
add_action('user_register', 'add_wallet_balance_to_user');
add_action('personal_options_update', 'add_wallet_balance_to_user');
add_action('edit_user_profile_update', 'add_wallet_balance_to_user');



// Add a Wallet menu item to My Account
function add_wallet_to_my_account_menu($items) {
    $items['wallet'] = __('کیف پول', 'woocommerce');
    // بازسازی ترتیب منو
    if (isset($items['customer-logout'])) {
        $logout = $items['customer-logout'];
        unset($items['customer-logout']); // حذف موقت آیتم خروج
    }
    // افزودن آیتم کیف پول قبل از خروج
    $items['customer-logout'] = $logout;
    return $items;
}
add_filter('woocommerce_account_menu_items', 'add_wallet_to_my_account_menu');

// Register the Wallet endpoint
function register_wallet_endpoint() {
    add_rewrite_endpoint('wallet', EP_ROOT | EP_PAGES);
}
add_action('init', 'register_wallet_endpoint');


function wallet_balance_endpoint_content() {
    $user_id = get_current_user_id();
    $balance = get_user_meta($user_id, 'wallet_balance', true) ?: 0;
    echo '<div class="walletParent">';
    echo '<div class="walletMainTitle">';
    echo '<h2>کیف پول الکترونیک</h2>';
    echo '<h3>' . __('موجودی کیف پول شما: ', 'woocommerce');
    echo  wc_price($balance)  . '</h3>';
    echo '</div>';
    echo '<div class="walletRightPart">';
    echo '<h4>افزایش موجودی کیف پولی</h4>';
    echo '<p>برای افزایش موجودی کیف پول خود کافی‌ست مبلغ را به تومان وارد کنید و به درگاه پرداخت وارد شوید، مبلغ پرداختی به موجودی کیف پول الکترونیک شما اضافه می‌شود.</p>';
    echo '</div>';
    echo '<form class="walletTopUp" method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
    echo '<input type="hidden" name="action" value="top_up_wallet">';
    echo '<p id="error-message" style="color: red;margin: 0;font-size: 11px;margin-right: auto;""></p>';
    echo '<input type="text" id="topUpAmount" name="top_up_amount" placeholder="مبلغ اعتبار" required />';
    wp_nonce_field('top_up_wallet_nonce', 'top_up_wallet_nonce_field');
    echo '<button type="submit">' . __('افزایش شارژ کیف پول', 'woocommerce') . '</button>';
    echo '</form>';
    echo '</div>';
}
add_action('woocommerce_account_wallet_endpoint', 'wallet_balance_endpoint_content');

// Add Wallet Balance field to admin user profile
function add_wallet_balance_to_admin($user) {
    $balance = get_user_meta($user->ID, 'wallet_balance', true) ?: 0;
    ?>
    <h3><?php _e('Wallet Balance', 'woocommerce'); ?></h3>
    <table class="form-table">
        <tr>
            <th><label for="wallet_balance"><?php _e('Balance', 'woocommerce'); ?></label></th>
            <td><input type="number" name="wallet_balance" id="wallet_balance" value="<?php echo esc_attr($balance); ?>" step="0.01" /></td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'add_wallet_balance_to_admin');
add_action('edit_user_profile', 'add_wallet_balance_to_admin');

// Save Wallet Balance from admin
function save_wallet_balance_from_admin($user_id) {
    if (isset($_POST['wallet_balance'])) {
        update_user_meta($user_id, 'wallet_balance', floatval($_POST['wallet_balance']));
    }
}
add_action('personal_options_update', 'save_wallet_balance_from_admin');
add_action('edit_user_profile_update', 'save_wallet_balance_from_admin');


function update_wallet_balance_on_order_complete($order_id) {
    $order = wc_get_order($order_id);
    if (!$order) {
        return;
    }

    foreach ($order->get_items() as $item) {
        $product = $item->get_product();

        // Check if the product SKU matches the wallet top-up SKU
        if ($product && $product->get_sku() === 'wallet_prod_virtual') {
            $user_id = $order->get_user_id();
            if (!$user_id) {
                return; // No user associated with the order
            }

            // Get the current balance
            $current_balance = get_user_meta($user_id, 'wallet_balance', true) ?: 0;

            // Get the top-up amount from the order item
            $top_up_amount = floatval($item->get_total());

            // Update the user's wallet balance
            update_user_meta($user_id, 'wallet_balance', $current_balance + $top_up_amount);

            // Optionally, add an order note for confirmation
            $order->add_order_note(__('Wallet balance updated by ' . wc_price($top_up_amount), 'woocommerce'));
        }
    }
}

add_action('woocommerce_order_status_completed', 'update_wallet_balance_on_order_complete');
add_action('woocommerce_order_status_processing', 'update_wallet_balance_on_order_complete');



// Display custom subtotal in the cart and checkout
// add_action('woocommerce_cart_totals_before_order_total', 'display_custom_subtotal_in_cart');
// add_action('woocommerce_review_order_before_order_total', 'display_custom_subtotal_in_checkout');



add_action('woocommerce_cart_calculate_fees', 'apply_custom_cart_subtotals', 10, 1);

function apply_custom_cart_subtotals($cart) {
    // Ensure the logic only applies on the checkout page
    if (!is_checkout() || is_admin() && !defined('DOING_AJAX')) {
        return;
    }

    // Check if the user opted to use the wallet credit
    $use_wallet_credit = WC()->session->get('use_wallet_credit', '0');
    if ($use_wallet_credit !== '1') {
        return; // Do nothing if the wallet credit is not enabled
    }

    // Get the current user wallet balance
    $user_id = get_current_user_id();
    $wallet_balance = (float)get_user_meta($user_id, 'wallet_balance', true);
    $cart_total = $cart->get_cart_contents_total();

    // Calculate wallet adjustment
    $wallet_adjustment = min($wallet_balance, $cart_total);
    $remaining_balance = $cart_total - $wallet_adjustment;

    // Store custom totals in session for later use
    WC()->session->set('custom_wallet_adjustment', $wallet_adjustment);
    WC()->session->set('custom_subtotal', $remaining_balance);

    // Apply the wallet adjustment to WooCommerce totals
    $cart->add_fee(__('پرداخت از اعتبار', 'woocommerce'), -$wallet_adjustment, true);
}



function display_custom_subtotal_in_cart() {
    $custom_subtotal = WC()->session->get('custom_subtotal', 0);
    echo '<tr class="custom-subtotal">
            <th>' . __('Custom Subtotal', 'woocommerce') . '</th>
            <td>' . wc_price($custom_subtotal) . '</td>
          </tr>';
}

function display_custom_subtotal_in_checkout() {
    display_custom_subtotal_in_cart();
}

// Add checkbox to cart and checkout pages
add_action('woocommerce_review_order_before_order_total', 'add_wallet_usage_checkbox'); // Checkout only

function add_wallet_usage_checkbox() {
    if (!is_checkout()) {
        return; // Ensure this runs only on the checkout page
    }

    // Get the current user wallet balance
    $user_id = get_current_user_id();
    $wallet_balance = (float) get_user_meta($user_id, 'wallet_balance', true);

    ?>
    <tr class="wallet-usage">
        <th><?php _e('پرداخت از کیف پول', 'woocommerce'); ?></th>
        <td>
            <input type="checkbox" id="use_wallet_credit" name="use_wallet_credit" value="1"
                <?php checked(WC()->session->get('use_wallet_credit'), '1'); ?>>
            <label for="use_wallet_credit">
                <?php 
                printf(
                    __('اعتبار کیف پول را برای این سفارش اعمال کنید. (موجودی فعلی: %s)', 'woocommerce'), 
                    wc_price($wallet_balance)
                ); 
                ?>
            </label>
        </td>
    </tr>
    <?php
}





// AJAX handler to save the wallet usage selection
add_action('wp_ajax_toggle_wallet_usage', 'toggle_wallet_usage');
add_action('wp_ajax_nopriv_toggle_wallet_usage', 'toggle_wallet_usage');

function toggle_wallet_usage() {
    // Check if the checkbox value is received
    $use_wallet_credit = isset($_POST['use_wallet_credit']) ? sanitize_text_field($_POST['use_wallet_credit']) : '0';

    // Save the selection to WooCommerce session
    WC()->session->set('use_wallet_credit', $use_wallet_credit);

    wp_send_json_success(['message' => 'Wallet usage updated.']);
}


add_action('woocommerce_checkout_order_processed', 'update_wallet_balance_after_order', 10, 1);

function update_wallet_balance_after_order($order_id) {
    if (!$order_id) return;

    $order = wc_get_order($order_id);
    if (!$order) return;

    $user_id = $order->get_user_id();
    if (!$user_id) return;

    // Check if wallet credit was used
    $use_wallet_credit = WC()->session->get('use_wallet_credit', '0');
    if ($use_wallet_credit !== '1') return;

    // Retrieve wallet adjustment from session
    $wallet_adjustment = WC()->session->get('custom_wallet_adjustment', 0);
    if ($wallet_adjustment <= 0) return;

    // Update the wallet balance
    $wallet_balance = (float)get_user_meta($user_id, 'wallet_balance', true);
    $new_wallet_balance = max(0, $wallet_balance - $wallet_adjustment);
    update_user_meta($user_id, 'wallet_balance', $new_wallet_balance);

    // Clear session data
    WC()->session->__unset('custom_wallet_adjustment');
    WC()->session->__unset('use_wallet_credit');
}

add_action('template_redirect', 'clear_wallet_session_on_cart');

function clear_wallet_session_on_cart() {
    if (is_cart()) {
        WC()->session->__unset('use_wallet_credit');
        WC()->session->__unset('custom_wallet_adjustment');
        WC()->session->__unset('custom_subtotal');
    }
}
