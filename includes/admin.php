<?php
// Add Custom Columns to sproduct Admin Table
function sproduct_add_custom_columns($columns) {
    $columns['form_data'] = 'Form Data';
    return $columns;
}
add_filter('manage_sproduct_posts_columns', 'sproduct_add_custom_columns');

function sproduct_custom_column_content($column, $post_id) {
    if ($column == 'form_data') {
        $form_data = get_post_meta($post_id, '_sproduct_form_data', true);
        echo $form_data ? 'Yes' : 'No';
    }
}
add_action('manage_sproduct_posts_custom_column', 'sproduct_custom_column_content', 10, 2);
