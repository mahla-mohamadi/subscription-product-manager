<?php
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
    $per_page = 1; // Number of rows per page
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
            echo '<td><a href="'.esc_url($editURL).'">' . esc_html($row->edit) . '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" xmlns="http://www.w3.org/2000/svg"><g stroke-width="0"/><g stroke-linecap="round" stroke-linejoin="round"/><g stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m21.28 6.4-9.54 9.54c-.95.95-3.77 1.39-4.4.76s-.2-3.45.75-4.4l9.55-9.55a2.58 2.58 0 1 1 3.64 3.65"/><path d="M11 4H6a4 4 0 0 0-4 4v10a4 4 0 0 0 4 4h11c2.21 0 3-1.8 3-4v-5"/></g></svg></a></td>';
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
                WHERE id = %d
                AND end_date >= CURDATE()",
                $subscription_id
            )
        );
        // $user_info = $wpdb->get_results(
        //     $wpdb->prepare(
        //         "SELECT meta_key, meta_value 
        //          FROM wp_usermeta 
        //          WHERE user_id = %d AND meta_key IN ('first_name', 'last_name')",
        //         $subscription->user_id
        //     ),
        //     OBJECT_K
        // );
        $fullUserName = get_user_meta( $subscription->user_id, 'first_name', true ).' '.get_user_meta( $subscription->user_id, 'last_name', true );
        if ($subscription) {
            $jalali_start_date = convert_to_jalali($subscription->start_date);
            $jalali_end_date = convert_to_jalali($subscription->end_date);
            $sproductName = get_the_title($subscription->sproduct_id);
            echo "<h1>ویرایش اشتراک</h1>";
            echo "<p>اشتراک {$subscription->plan} {$fullUserName} ({$subscription->remaining_days} روز باقی مانده)</p>";
            echo "<p>نام سرویس: {$sproductName}</p>";
            echo "<p>تاریخ شروع: {$jalali_start_date}</p>";
            echo "<p>تاریخ پایان: {$jalali_end_date}</p>";
            echo "<p>وضعیت: {$subscription->status}</p>";
            echo "<p>تاریخ ایجاد: {$subscription->created_at}</p>";

            // Add a form for editing if needed
            echo '<form method="post">';
            echo '<label for="name">نام:</label>';
            echo '<input type="text" id="name" name="name" value="' . esc_attr($subscription->name) . '">';
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
                        <label>نام طرح</label>
                        <input type="text" name="sproduct_plans[<?php echo $index; ?>][name]"
                            value="<?php echo esc_attr($plan['name'] ?? ''); ?>" required />

                        <label>مدت طرح</label>
                        <input type="number" class="days-field" name="sproduct_plans[<?php echo $index; ?>][days]"
                            value="<?php echo esc_attr($plan['days'] ?? ''); ?>" required />

                        <label>قیمت طرح</label>
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