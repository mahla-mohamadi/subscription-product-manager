<?php
// Add Metabox for Form Builder
function sproduct_add_metabox() {
    add_meta_box(
        'sproduct_form_builder',
        'فرم ساز',
        'sproduct_form_builder_callback',
        'sproduct',
        'normal',
        'high'
    );
    add_meta_box(
        'sproduct_form_condition',
        'نمایش شرطی',
        'sproduct_form_condition_callback',
        'sproduct',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'sproduct_add_metabox');

// Render Metabox (Form Builder UI)
function sproduct_form_builder_callback($post) {
    wp_nonce_field('sproduct_save_form', 'sproduct_nonce');
    $form_data = get_post_meta($post->ID, '_sproduct_form_data', true);
    ?>

    <div id="sproduct-form-builder">
        <div class="form-steps">
            <!-- Steps will be added here dynamically -->
        </div>
        <button type="button" id="add-step-btn" class="button button-primary">+</button>
    </div>

    <!-- Hidden Field to Store Form Data -->
    <textarea name="sproduct_form_data" id="sproduct_form_data" style="display:none;">
        <?php echo esc_textarea($form_data); ?>
    </textarea>

    <script>
        // Pass form data from PHP to JavaScript
        window.sproductFormData = <?php echo $form_data ? $form_data : '[]'; ?>;
    </script>

    <?php
}
function sproduct_form_condition_callback($post){
    wp_nonce_field('sproduct_save_condition', 'sproduct_condition_nonce');
    $condition_data = get_post_meta($post->ID, '_sproduct_condition_data', true);
    ?>
    <h4>برای نمایش کامل فیلدها یک بار بروزرسانی کنید</h4>
    <div class="conditionContainer">
        <div class="conditionRow"></div>
        <div class="button-primary add-condition" id="add-condition">افزودن شرط</div>
    </div>
    <textarea name="sproduct_condition_data" id="sproduct_condition_data" style="display:none;">
        <?php echo esc_textarea($condition_data); ?>
    </textarea>
    <script>
        window.sproductConditionData = <?php echo $condition_data ? $condition_data : '[]'; ?>;
    </script>
    <?php
}

// Enqueue Admin Scripts (Sortable.js and admin.js)
function sproduct_enqueue_admin_scripts($hook) {
    global $post;
    // Load scripts only on post edit screens for sproduct
    if ($hook === 'post.php' || $hook === 'post-new.php') {
        if ('sproduct' === get_post_type($post)) {
            // Enqueue Sortable.js for drag-and-drop reordering
            wp_enqueue_script(
                'sproduct-sortable', 
                SPRODUCT_URL . 'assets/sortable.min.js', 
                [], 
                null, 
                true
            );
            // Enqueue Select2 CSS
            wp_enqueue_style(
                'select2-css', 
                SPRODUCT_URL . 'assets/select2.min.css', 
                [], 
                null
            );

            // Enqueue Select2 JavaScript
            wp_enqueue_script(
                'select2-js', 
                SPRODUCT_URL . 'assets/select2.min.js', 
                ['jquery'], 
                null, 
                true
            );
            // Enqueue admin.js for form builder logic
            wp_enqueue_script(
                'sproduct-admin-js', 
                SPRODUCT_URL . 'assets/admin.js', 
                ['jquery'], 
                null, 
                true
            );
        }
    }
}
add_action('admin_enqueue_scripts', 'sproduct_enqueue_admin_scripts');

// Save Form Data (Post Meta) on Save
function sproduct_save_form($post_id) {
    // Verify nonce for security
    if (!isset($_POST['sproduct_form_nonce']) || !wp_verify_nonce($_POST['sproduct_form_nonce'], 'sproduct_save_form') || !isset($_POST['sproduct_condition_nonce']) || !wp_verify_nonce($_POST['sproduct_condition_nonce'], 'sproduct_save_condition')) {
        return;
    }

    // Prevent saving during autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Ensure user has the correct capability
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save Form Data
    if (isset($_POST['sproduct_form_data'])) {
        $form_data = wp_unslash($_POST['sproduct_form_data']);  // Decode slashed data
        update_post_meta($post_id, '_sproduct_form_data', $form_data);
    }
    if (isset($_POST['sproduct_condition_data'])) {
        $condition_data = wp_unslash($_POST['sproduct_condition_data']);  // Decode slashed data
        update_post_meta($post_id, '_sproduct_condition_data', $condition_data);
    }
}
add_action('save_post', 'sproduct_save_form');
