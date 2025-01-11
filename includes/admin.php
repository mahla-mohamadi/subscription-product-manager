<?php
function create_hidden_virtual_product()
{
    if (class_exists('WC_Product')) {
        if (!wc_get_product_id_by_sku('s_prod_virtual')) {
            $product = new WC_Product();
            $product->set_name('اشتراک');
            $product->set_status('publish');
            $product->set_catalog_visibility('hidden');
            $product->set_virtual(true);  // Set as virtual product
            $product->set_price(1);
            $product->set_regular_price(1);
            $product->set_sku('s_prod_virtual');
            $product->save();
        }
    }
}
add_action('admin_init', 'create_hidden_virtual_product');


function register_subscriptions_menu()
{
    add_menu_page(
        'اشتراک ها', // Page title
        'اشتراک ها', // Menu title
        'manage_options', // Capability
        'subscriptions',  // Menu slug
        'subscriptions_page_callback', // Callback function
        'dashicons-list-view', // Icon
        25 // Position
    );
}
add_action('admin_menu', 'register_subscriptions_menu');

function subscriptions_page_callback() {
    global $wpdb;

    $table_name = $wpdb->prefix . 's_subscriptions';

    // Handle pagination
    $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $per_page = 10; // Number of rows per page
    $offset = ($paged - 1) * $per_page;

    // Handle sorting
    $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'id';
    $order = isset($_GET['order']) && strtolower($_GET['order']) === 'asc' ? 'ASC' : 'DESC';

    // Fetch data
    $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d",
            $per_page,
            $offset
        )
    );

    // Calculate pagination
    $total_pages = ceil($total_items / $per_page);

    // Display the table
    echo '<div class="wrap">';
    echo '<h1>لیست اشتراک ها</h1>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead>';
    echo '<tr>';

    // Define sortable columns and display the sorting icons
    $columns = [
        'user_id' => 'شناسه کاربر',
        'sproduct_id' => 'شناسه محصول',
        'start_date' => 'شروع اشتراک',
        'end_date' => 'پایان اشتراک',
        'amount' => 'هزینه',
        'plan' => 'پلن',
        'status' => 'وضعیت'
    ];

    foreach ($columns as $column => $label) {
        // Determine the opposite order for sorting
        $sort_order = ($orderby === $column && $order === 'ASC') ? 'DESC' : 'ASC';
        // Set the sorting icon based on the current sorting order
        $icon = '';
        if ($orderby === $column) {
            // If current order is asc, show the down arrow, otherwise show the up arrow
            $icon = ($order === 'asc') 
                ? '<span class="dashicons dashicons-arrow-up-alt2"></span>' 
                : '<span class="dashicons dashicons-arrow-down-alt2"></span>';
        }

        // Create URL with sorting parameters
        $url = add_query_arg([
            'page' => 'subscriptions',
            'orderby' => $column,
            'order' => $sort_order
        ]);
        
        echo '<th scope="col"><a href="' . esc_url($url) . '">' . $label . ' ' . $icon . '</a></th>';
    }

    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    if (!empty($results)) {
        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row->user_id) . '</td>';
            echo '<td>' . esc_html($row->sproduct_id) . '</td>';
            echo '<td>' . esc_html($row->start_date) . '</td>';
            echo '<td>' . esc_html($row->end_date) . '</td>';
            echo '<td>' . esc_html($row->amount) . '</td>';
            echo '<td>' . esc_html($row->plan) . '</td>';
            echo '<td>' . esc_html($row->status) . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="7">No subscriptions found.</td></tr>';
    }
    echo '</tbody>';
    echo '</table>';

    // Display pagination
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
        'Subscription Plans',
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
                        <label>Plan Name</label>
                        <input type="text" name="sproduct_plans[<?php echo $index; ?>][name]"
                            value="<?php echo esc_attr($plan['name'] ?? ''); ?>" required />

                        <label>Days</label>
                        <input type="number" class="days-field" name="sproduct_plans[<?php echo $index; ?>][days]"
                            value="<?php echo esc_attr($plan['days'] ?? ''); ?>" required />

                        <label>Price</label>
                        <input type="number" class="price-field" name="sproduct_plans[<?php echo $index; ?>][price]"
                            value="<?php echo esc_attr($plan['price'] ?? ''); ?>" required />

                        <label>Description</label>
                        <textarea
                            name="sproduct_plans[<?php echo $index; ?>][description]"><?php echo esc_textarea($plan['description'] ?? ''); ?></textarea>

                        <button type="button" class="remove-plan button">Remove</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <button type="button" id="add-plan" class="button">+ Add Plan</button>
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