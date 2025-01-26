<?php




// Schedule the cron job for midnight daily.
function schedule_subscription_status_check() {
    if (!wp_next_scheduled('check_subscription_status')) {
        // Schedule the event to run daily at midnight.
        wp_schedule_event(strtotime('00:00:00'), 'daily', 'check_subscription_status');
    }
}
add_action('wp', 'schedule_subscription_status_check');

// Clear the cron event upon plugin/theme deactivation.
function clear_subscription_status_check() {
    $timestamp = wp_next_scheduled('check_subscription_status');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'check_subscription_status');
    }
}
register_deactivation_hook(__FILE__, 'clear_subscription_status_check');




function update_subscription_status() {
    global $wpdb;

    // Define the table name (use the appropriate table prefix).
    $table_name = $wpdb->prefix . 's_subscriptions';

    // Get the current date in the correct format.
    $today_date = current_time('mysql'); // Outputs 'YYYY-MM-DD HH:MM:SS'.

    // Update the status of subscriptions where end_date has passed and the status is 'active'.
    $result = $wpdb->query(
        $wpdb->prepare(
            "UPDATE $table_name 
            SET status = %s 
            WHERE end_date < %s 
            AND status = %s",
            'deactive', // New status value.
            $today_date, // Compare against the current date.
            'active' // Old status value.
        )
    );

    // Debugging: Log the query result (optional).
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('Subscription status update result: ' . $result);
    }
}
add_action('check_subscription_status', 'update_subscription_status');




function convert_to_jalali($gregorian_date) {
    require_once SPRODUCT_PATH . 'lib/jalali-3.4.2/src/Jalalian.php';
    if (!$gregorian_date) {
        return 'Invalid date';
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $gregorian_date)) {
        return 'Invalid date format. Expected format: Y-m-d';
    }
    if (!class_exists('Morilog\Jalali\Jalalian')) {
        return 'Jalalian class not found';
    }
    $jalali_date = Morilog\Jalali\Jalalian::forge($gregorian_date)->format('Y/m/d');
    return $jalali_date;
}
function create_hidden_virtual_product(){
    if (class_exists('WC_Product')) {
        if (!wc_get_product_id_by_sku('s_prod_virtual')) {
            $product = new WC_Product();
            $product->set_name('اشتراک');
            $product->set_status('publish');
            $product->set_catalog_visibility('hidden');
            $product->set_virtual(true);
            $product->set_price(1);
            $product->set_regular_price(1);
            $product->set_sku('s_prod_virtual');
            $product->save();
        }
    }
}
add_action('admin_init', 'create_hidden_virtual_product');

function register_subscriptions_menu(){
    add_menu_page(
        'اشتراک ها', // Page title
        'اشتراک ها', // Menu title
        'manage_options', // Capability
        'subscriptions',  // Menu slug
        'subscriptions_page_callback', // Callback function
        'dashicons-list-view', // Icon
        25 // Position
    );
    add_submenu_page(
        null, // Parent slug (null hides this submenu from the menu)
        'ویرایش اشتراک', // Page title
        'ویرایش اشتراک', // Menu title (not visible)
        'manage_options', // Capability
        'edit-subscription', // Menu slug
        'edit_subscription_page_callback' // Callback function
    );
}
add_action('admin_menu', 'register_subscriptions_menu');

function subscriptions_page_callback(){
    global $wpdb;
    $table_name = $wpdb->prefix . 's_subscriptions';
    $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $per_page = 2; // Number of rows per page
    $offset = ($paged - 1) * $per_page;
    $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'id';
    $order = isset($_GET['order']) && strtolower($_GET['order']) === 'asc' ? 'ASC' : 'DESC';
    $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d",
            $per_page,
            $offset
        )
    );
    $total_pages = ceil($total_items / $per_page);
    echo '<div class="wrap">';
    echo '<h1>لیست اشتراک ها</h1>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead>';
    echo '<tr>';
    $columns = [
        'user_id' => 'کاربر',
        'sproduct_id' => 'اشتراک',
        'start_date' => 'شروع اشتراک',
        'end_date' => 'پایان اشتراک',
        'amount' => 'هزینه',
        'plan' => 'پلن',
        'status' => 'وضعیت',
        'edit' => 'ویرایش'
    ];
    foreach ($columns as $column => $label) {
        $sort_order = ($orderby === $column && $order === 'ASC') ? 'DESC' : 'ASC';
        $icon = '';
        if ($orderby === $column) {
            $icon = ($order === 'asc')
                ? '<span class="dashicons dashicons-arrow-up-alt2"></span>'
                : '<span class="dashicons dashicons-arrow-down-alt2"></span>';
        }
        $url = add_query_arg([
            'page' => 'subscriptions',
            'orderby' => $column,
            'order' => $sort_order
        ]);
        echo '<th scope="col"><a href="' . esc_url($url) . '">' . $label . ' ' . $icon . '</a></th>';
    }
    echo '</tr>';
    echo '</thead>';
    echo '<tbody class="subscriptionsTableMainBody">';
    if (!empty($results)) {

        foreach ($results as $row) {
            $editURL = admin_url('admin.php?page=edit-subscription&subscription_id=' . $row->id);
            $fullUserName = get_user_meta( $row->user_id, 'first_name', true ).' '.get_user_meta( $row->user_id, 'last_name', true );
            echo '<tr>';
            echo '<td>' . esc_html($fullUserName) . '</td>';
            echo '<td>' . esc_html($row->sproduct_name) . '</td>';
            echo '<td>' . esc_html($row->start_date) . '</td>';
            echo '<td>' . esc_html($row->end_date) . '</td>';
            echo '<td>' . esc_html($row->amount) . ' تومان</td>';
            echo '<td>' . esc_html($row->plan) . '</td>';
            if (esc_html($row->status) == "active") {
                echo '<td class="activeButton"><span>فعال</span></td>';
            } else {
                echo '<td class="DeactiveButton"><span>غیر فعال</span></td>';
            }
            echo '<td><a href="'.esc_url($editURL).'"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" xmlns="http://www.w3.org/2000/svg"><g stroke-width="0"/><g stroke-linecap="round" stroke-linejoin="round"/><g stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m21.28 6.4-9.54 9.54c-.95.95-3.77 1.39-4.4.76s-.2-3.45.75-4.4l9.55-9.55a2.58 2.58 0 1 1 3.64 3.65"/><path d="M11 4H6a4 4 0 0 0-4 4v10a4 4 0 0 0 4 4h11c2.21 0 3-1.8 3-4v-5"/></g></svg></a></td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="7">No subscriptions found.</td></tr>';
    }
    echo '</tbody>';
    echo '</table>';
    echo '<div class="tablenav bottom">';
    echo '<div class="tablenav-pages">';
    if ($total_pages > 1) {
        $base = add_query_arg('paged', '%#%', remove_query_arg('orderby', remove_query_arg('order')));
        echo paginate_links([
            'base' => $base,
            'format' => '',
            'current' => $paged,
            'total' => $total_pages,
            'prev_text' => '&laquo;',
            'next_text' => '&raquo;',
        ]);
    }
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
function edit_subscription_page_callback(){
    global $wpdb;

    if (isset($_GET['subscription_id'])) {
        $subscription_id = intval($_GET['subscription_id']); // Sanitize the ID

        // Query the wp_s_subscription table
        $table_name = $wpdb->prefix . 's_subscriptions'; // Adjust table name with prefix
        $subscription = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT *, DATEDIFF(end_date, CURDATE()) AS remaining_days
                FROM $table_name
                WHERE id = %d",
                $subscription_id
            )
        );
        $suspensionSubs = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT *, DATEDIFF(end_date, CURDATE()) AS remaining_days
                FROM $table_name
                WHERE id = %d
                AND end_date >= CURDATE()",
                $subscription_id
            )
        );
        $fullUserName = get_user_meta( $subscription->user_id, 'first_name', true ).' '.get_user_meta( $subscription->user_id, 'last_name', true );
        if ($subscription) {
            $subscription_start_date = $subscription->start_date;
            $subscription_end_date = $subscription->end_date;
            $jalali_start_date = convert_to_jalali($subscription_start_date);
            $jalali_end_date = convert_to_jalali($subscription_end_date);
            $sproductName = get_the_title($subscription->sproduct_id);
            $formData = json_decode(stripslashes($subscription->formdata),true);
            if (esc_html($subscription->status) == "active") {
                $statusBadge = '<span style="background-color: #d2ffc9;padding: 3px 10px;border-radius: 2px;color: #384a34;border: 1px solid #bceeb2;">فعال</span>';
            } else {
                $statusBadge = '<span style="background-color: #ffc9c9;padding: 3px 10px;border-radius: 2px;color: #4a3434;border: 1px solid #eeb2b2;" class="DeactiveButton">غیر فعال</span>';
            }
            echo "<h1>ویرایش اشتراک</h1>";
            if (esc_html($subscription->status) == "active") {
                echo "<p>اشتراک {$subscription->plan} {$fullUserName} ({$subscription->remaining_days} روز باقی مانده)</p>";
            } else {
                echo "<p>اشتراک در وضعیت تعلیق قرار دارد</p>";
            }
            echo "<p>نام سرویس: {$sproductName}</p>";
            echo "<p>تاریخ شروع: {$jalali_start_date}</p>";
            echo "<p>تاریخ پایان: {$jalali_end_date}</p>";
            echo "<p>هزینه تمدید: {$subscription->amount} تومان</p>";
            echo "<p>وضعیت: {$statusBadge}</p>";
            if ($formData && is_array($formData)) {
                echo '<table border="1" style="border-collapse: collapse; width: 100%;">';
                echo '<tr><th>Field</th><th>Value</th></tr>';
                foreach ($formData as $key => $value) {
                    echo '<tr>';
                    echo '<td>' . esc_html($key) . '</td>';
                    if (is_array($value)) {
                        echo '<td>' . esc_html(implode(', ', $value)) . '</td>';
                    } else {
                        echo '<td>' . esc_html($value) . '</td>'; // Field value
                    }
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                echo 'No form data available.';
            }
            echo "<h2>پرداخت‌های کاربر</h2>";
            $user_id = $subscription->user_id;
            $skus_to_search = ['s_prod_virtual', 'wallet_prod_virtual'];
            $hidden_virtual_product_ids = [];
            // Query WooCommerce to get product IDs by SKUs
            foreach ($skus_to_search as $sku) {
                $product = wc_get_product_id_by_sku($sku);
                if ($product) {
                    $hidden_virtual_product_ids[] = $product;
                }
            }
            // If no products were found, display an error message
            if (empty($hidden_virtual_product_ids)) {
                echo "<p>محصولات مجازی مخفی با این شناسه‌ها پیدا نشدند.</p>";
                return;
            }
            $meta_key = '_subscription_id';
            $meta_value = $subscription->id;
            $query = "
                SELECT DISTINCT order_id
                FROM {$wpdb->prefix}woocommerce_order_itemmeta AS oim
                INNER JOIN {$wpdb->prefix}woocommerce_order_items AS oi
                ON oim.order_item_id = oi.order_item_id
                WHERE oim.meta_key = %s
                AND oim.meta_value = %s
            ";

            $order_ids = $wpdb->get_col($wpdb->prepare($query, $meta_key, $meta_value));
            // Query WooCommerce orders
            // دریافت سفارش‌های کاربر
            // $customer_orders = wc_get_orders(array(
            //     'customer_id' => $user_id,
            //     'limit' => -1, // بدون محدودیت
            //     'orderby' => 'date',
            //     'order' => 'DESC',
            // ));

            // if ($customer_orders) {
            //     echo "<table border='1' style='width:100%; text-align: center;'>";
            //     echo "<tr>
            //             <th>سریال سفارش</th>
            //             <th>نام محصول</th>
            //             <th>مبلغ</th>
            //             <th>تاریخ</th>
            //         </tr>";
            
            //     $orders_found = false; // Track if any relevant orders are found
            
            //     foreach ($customer_orders as $order) {
            //         $order_id = $order->get_id();
            //         $order_date = $order->get_date_created();
            //         $formatted_date = $order_date ? $order_date->date('Y-m-d') : '';

            //         // Check if the order date is within the subscription period
            //         // if ($formatted_date > $subscription_end_date || $formatted_date <= $subscription_start_date) {
            //         //     continue; // Skip orders outside the subscription period
            //         // }

            //         $newDate = convert_to_jalali($formatted_date);
            //         $contains_virtual_product = false; // Flag to check if the order contains a virtual product
            
            //         // First pass: Check if the order contains any virtual product
            //         foreach ($order->get_items() as $item) {
            //             $product_id = $item->get_product_id();
            //             if (in_array($product_id, $hidden_virtual_product_ids)) {
            //                 $contains_virtual_product = true;
            //                 break; // Stop checking once a virtual product is found
            //             }
            //         }
            
            //         // If the order contains a virtual product, display all products in the order
            //         if ($contains_virtual_product) {
            //             foreach ($order->get_items() as $item) {
            //                 $product_id = $item->get_product_id();
            //                 $product_name = $item->get_name(); // Get product name
            //                 $item_total = $item->get_total(); // Get item's total price
            //                 $product_sku = get_post_meta($product_id, '_sku', true);
            
            //                 // Customize virtual product name if needed
            //                 if (in_array($product_id, $hidden_virtual_product_ids)) {
            //                     if ($product_sku === 's_prod_virtual') {
            //                         $product_name = "اشتراک {$subscription->plan}";
            //                     }
            //                 }
            
            //                 // Mark as orders found and display the product
            //                 $orders_found = true;
            //                 echo "<tr>
            //                         <td>{$order_id}</td>
            //                         <td>{$product_name}</td>
            //                         <td>{$item_total} تومان</td>
            //                         <td>{$newDate}</td>
            //                     </tr>";
            //             }
            //         }
            //     }
            
            //     if (!$orders_found) {
            //         echo "<tr><td colspan='4'>هیچ سفارشی با محصولات مجازی مورد نظر پیدا نشد.</td></tr>";
            //     }
            //     echo "</table>";
            // } else {
            //     echo "<p>این کاربر سفارشی ندارد.</p>";
            // }
            if (!empty($order_ids)) {
                echo '<table border="1" style="border-collapse: collapse; width: 100%;">';
                echo '<tr><th>Order ID</th><th>Paid Amount</th><th>Date</th></tr>';
            
                foreach ($order_ids as $order_id) {
                    // Load the order object
                    $order = wc_get_order($order_id);
            
                    if ($order) {
                        // Get order details
                        $order_date = $order->get_date_created(); // Date created
                        $order_date_formatted = $order_date->date('Y-m-d H:i:s');
                        $order_date_jalali = convert_to_jalali(explode(' ',$order_date_formatted)[0]);
                        $paid_amount = $order->get_total();      // Total paid
                        // Display order details in the table
                        echo '<tr>';
                        echo '<td>' . esc_html($order_id) . '</td>';
                        echo '<td>' . wc_price($paid_amount) . '</td>';
                        echo '<td>' . esc_html($order_date_jalali) . ' ساعت '.explode(' ',$order_date_formatted)[1].'</td>';
                        echo '</tr>';
                    }
                }
            
                echo '</table>';
            } else {
                echo 'No orders found with the specified item meta.';
            }
            


            // Add a form for editing if needed
            echo '<form method="post">';
            echo '<input type="hidden" name="edit_hidden_id" id="edit_hidden_id" value="'.esc_attr($subscription->id).'">';
            echo '<label for="edit_sub_plan">پلن:</label>';
            echo '<input type="text" id="edit_sub_name" name="edit_sub_name" value="' . esc_attr($subscription->name) . '">';
            echo '<br>';
            echo '<label for="status">وضعیت:</label>';
            echo '<input type="text" id="status" name="status" value="' . esc_attr($subscription->status) . '">';
            echo '<br>';
            echo '<input type="submit" name="update_subscription" value="بروزرسانی">';
            echo '</form>';

            // Handle form submission
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_subscription'])) {
                $new_name = sanitize_text_field($_POST['name']);
                $new_status = sanitize_text_field($_POST['status']);

                $update_result = $wpdb->update(
                    $table_name,
                    [
                        'name' => $new_name,
                        'status' => $new_status
                    ],
                    ['id' => $subscription_id],
                    ['%s', '%s'], // Data format for the fields being updated
                    ['%d']        // Data format for the WHERE clause
                );

                if ($update_result !== false) {
                    echo '<p style="color:green;">اشتراک با موفقیت بروزرسانی شد.</p>';
                } else {
                    echo '<p style="color:red;">خطایی در بروزرسانی رخ داد.</p>';
                }
            }
        } else {
            echo "<h1>خطا</h1>";
            echo "<p>اشتراک با این ID یافت نشد.</p>";
        }
    } else {
        echo "<h1>خطا</h1>";
        echo "<p>ID اشتراک مشخص نشده است.</p>";
    }
}


function sproduct_add_custom_columns($columns)
{
    $columns['form_data'] = 'Form Data';
    return $columns;
}
add_filter('manage_sproduct_posts_columns', 'sproduct_add_custom_columns');

function sproduct_custom_column_content($column, $post_id)
{
    if ($column == 'form_data') {
        $form_data = get_post_meta($post_id, '_sproduct_form_data', true);
        echo $form_data ? 'Yes' : 'No';
    }
}
add_action('manage_sproduct_posts_custom_column', 'sproduct_custom_column_content', 10, 2);

// Enqueue Admin Styles for Repeater Fields
function sproduct_enqueue_admin_styles($hook)
{
    global $post;

    // Only load on 'sproduct' edit screens
    if ($hook === 'post.php' || $hook === 'post-new.php') {
        if ('sproduct' === get_post_type($post)) {
            wp_enqueue_style(
                'sproduct-admin-style',
                SPRODUCT_URL . 'assets/admin-style.css',
                [],
                filemtime(SPRODUCT_PATH . 'assets/admin-style.css')
            );
        }
    }
    if (isset($_GET['page']) && $_GET['page'] === 'subscriptions') {
        wp_enqueue_style(
            'subscriptions-admin-style',
            SPRODUCT_URL . 'assets/admin-style.css', // همان فایل یا فایل متفاوت
            [],
            filemtime(SPRODUCT_PATH . 'assets/admin-style.css')
        );
    }
}
add_action('admin_enqueue_scripts', 'sproduct_enqueue_admin_styles');

// Enqueue Admin Repeater JS for sproduct Post Type
function sproduct_enqueue_admin_repeater_js($hook)
{
    global $post;

    // Only load on post edit screens for sproduct
    if ($hook === 'post.php' || $hook === 'post-new.php') {
        if ('sproduct' === get_post_type($post)) {
            wp_enqueue_script(
                'sproduct-admin-repeater-js',
                SPRODUCT_URL . 'assets/admin-repeater.js',
                ['jquery'],  // jQuery as a dependency
                filemtime(SPRODUCT_PATH . 'assets/admin-repeater.js'),
                true  // Load in the footer
            );
        }
    }
}
add_action('admin_enqueue_scripts', 'sproduct_enqueue_admin_repeater_js');


// Add Metabox for Plan Repeater (Separate from Form Builder)
function sproduct_add_plan_repeater_metabox()
{
    add_meta_box(
        'sproduct_plan_repeater',
        'طرح های اشتراک',
        'sproduct_plan_repeater_callback',
        'sproduct',
        'normal',  // Display below the content editor
        'high'     // High priority (appears above normal boxes)
    );
}
add_action('add_meta_boxes', 'sproduct_add_plan_repeater_metabox');

// Render the Repeater Metabox
function sproduct_plan_repeater_callback($post)
{
    wp_nonce_field('sproduct_save_plan_repeater', 'sproduct_repeater_nonce');

    // Get the plans from post meta and unserialize
    $plans = get_post_meta($post->ID, '_sproduct_plans', true);
    $plans = maybe_unserialize($plans);
    $plans = is_array($plans) ? $plans : [];
    ?>

    <div id="plan-repeater-container">
        <div id="plan-repeater">
            <?php if (!empty($plans)): ?>
                <?php foreach ($plans as $index => $plan): ?>
                    <div class="plan-item">
                        <label>نام</label>
                        <input type="text" name="sproduct_plans[<?php echo $index; ?>][name]"
                            value="<?php echo esc_attr($plan['name'] ?? ''); ?>" required />

                        <label>مدت</label>
                        <input type="number" class="days-field" name="sproduct_plans[<?php echo $index; ?>][days]"
                            value="<?php echo esc_attr($plan['days'] ?? ''); ?>" required />

                        <label>قیمت</label>
                        <input type="number" class="price-field" name="sproduct_plans[<?php echo $index; ?>][price]"
                            value="<?php echo esc_attr($plan['price'] ?? ''); ?>" required />

                        <label>توضیحات</label>
                        <textarea
                            name="sproduct_plans[<?php echo $index; ?>][description]"><?php echo esc_textarea($plan['description'] ?? ''); ?></textarea>

                        <button type="button" class="remove-plan button">حذف</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <button type="button" id="add-plan" class="button">افزودن طرح +</button>
    </div>
    <?php
}



// Save Repeater Data when Post is Updated
function sproduct_save_plan_repeater($post_id)
{
    // Security Check
    if (!isset($_POST['sproduct_repeater_nonce']) || !wp_verify_nonce($_POST['sproduct_repeater_nonce'], 'sproduct_save_plan_repeater')) {
        return;
    }

    // Prevent Autosaves
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check User Permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Ensure plan data exists and is an array
    if (isset($_POST['sproduct_plans']) && is_array($_POST['sproduct_plans'])) {
        $plans = [];

        // Loop through each plan and sanitize fields
        foreach ($_POST['sproduct_plans'] as $plan) {
            $plans[] = [
                'name' => sanitize_text_field($plan['name']),
                'days' => absint($plan['days']),  // Only positive integers
                'price' => absint($plan['price']),
                'description' => sanitize_textarea_field($plan['description']),
            ];
        }

        // Save as serialized array to prevent double encoding
        update_post_meta($post_id, '_sproduct_plans', maybe_serialize($plans));
    } else {
        // If no plans exist, remove the meta to avoid empty data
        delete_post_meta($post_id, '_sproduct_plans');
    }
}
add_action('save_post', 'sproduct_save_plan_repeater');