<?php

add_filter('woocommerce_account_menu_items', 'add_custom_menu_item_to_middle');
function add_custom_menu_item_to_middle($items)
{
    // ایجاد آرایه جدید برای تغییر ترتیب
    $new_items = array();

    // قرار دادن گزینه‌ها قبل از آیتم جدید
    foreach ($items as $key => $value) {
        $new_items[$key] = $value;

        // وقتی به گزینه دلخواه (مثلاً سفارشات) رسیدیم، گزینه جدید را اضافه می‌کنیم
        if ($key === 'orders') {
            $new_items['subscription-item'] = __('اشتراک ها', 'textdomain');
        }
    }

    return $new_items;
}

$endpoint = "subscription-item";

add_action('init', 'add_custom_endpoint');
function add_custom_endpoint()
{
    add_rewrite_endpoint('subscription-item', EP_ROOT | EP_PAGES);
}


add_action('woocommerce_account_' . $endpoint . '_endpoint', 'custom_page_content');


function custom_page_content()
{
    global $wpdb;

    $current_user_id = get_current_user_id();

    if (!$current_user_id) {
        echo '<div class="notice notice-error"><p>شما به این صفحه دسترسی ندارید.</p></div>';
        return;
    }


    $table_name = $wpdb->prefix . 's_subscriptions';
    $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $per_page = 3; // Number of rows per page
    $offset = ($paged - 1) * $per_page;
    $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'id';
    $order = isset($_GET['order']) && strtolower($_GET['order']) === 'asc' ? 'ASC' : 'DESC';


    $total_items = $wpdb->get_var(
        $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE user_id = %d", $current_user_id)
    );

    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d ORDER BY $orderby $order LIMIT %d OFFSET %d",
            $current_user_id,
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
        'status' => 'وضعیت',
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
    echo '<tbody class="subsTablProfile">';
    if (!empty($results)) {
        foreach ($results as $row) {
            $editURL = admin_url('admin.php?page=edit-subscription&subscription_id=' . $row->id);
            $fullUserName = get_user_meta($row->user_id, 'first_name', true) . ' ' . get_user_meta($row->user_id, 'last_name', true);
            echo '<tr>';
            echo '<td>' . esc_html($fullUserName) . '</td>';
            echo '<td>' . esc_html($row->plan) . '</td>';
            echo '<td>' . esc_html($row->start_date) . '</td>';
            echo '<td>' . esc_html($row->end_date) . '</td>';
            echo '<td>' . esc_html($row->amount) . ' تومان</td>';
            if (esc_html($row->status) == "active") {
                echo '<td class="activeButton"><span>فعال</span></td>';
            } else {
                echo '<td class="DeactiveButton"><span>غیر فعال</span></td>';
            }
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

add_action('after_switch_theme', 'flush_rewrite_rules');