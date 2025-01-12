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
    if (!isset($_POST['sproduct_nonce']) || !wp_verify_nonce($_POST['sproduct_nonce'], 'sproduct_save_form')) {
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
}
add_action('save_post', 'sproduct_save_form');
