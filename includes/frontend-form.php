<?php
// Inject Form into Single sproduct Posts
function sproduct_display_form_on_single($content) {
    if (is_singular('sproduct') && in_the_loop() && is_main_query()) {
        global $post;
        $form_data = get_post_meta($post->ID, '_sproduct_form_data', true);

        if (!$form_data) {
            return $content . '<p>No form data found.</p>';
        }

        $form_data = json_decode($form_data, true);
        ob_start();
        ?>
        <div id="sproduct-form-frontend" data-post-id="<?php echo esc_attr($post->ID); ?>">
            <form id="sproduct-main-form">
                <?php foreach ($form_data as $step_index => $step) : ?>
                    <div class="sproduct-step" data-step="<?php echo $step_index; ?>" <?php echo $step_index !== 0 ? 'style="display:none;"' : ''; ?>>
                        <h3><?php echo esc_html($step['name']); ?></h3>
                        <?php foreach ($step['inputs'] as $input_index => $input) : ?>
                            <div class="sproduct-input">
                                <label><?php echo esc_html($input['label']); ?></label>
                                <?php if ($input['type'] === 'text' || $input['type'] === 'email') : ?>
                                    <input type="<?php echo esc_attr($input['type']); ?>" 
                                           name="sproduct_input_<?php echo $step_index; ?>_<?php echo $input_index; ?>" 
                                           required="<?php echo $input['required'] ? 'required' : ''; ?>" />
                                <?php elseif ($input['type'] === 'checkbox') : ?>
                                    <input type="checkbox" 
                                           name="sproduct_input_<?php echo $step_index; ?>_<?php echo $input_index; ?>" 
                                           value="1" />
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
                <div class="sproduct-navigation">
                    <button type="button" id="prev-btn" disabled>Previous</button>
                    <button type="button" id="next-btn">Next</button>
                    <button type="submit" id="submit-btn" style="display:none;">Submit</button>
                </div>
            </form>
        </div>
        <?php
        $form_html = ob_get_clean();
        return $content . $form_html;
    }
    return $content;
}
add_filter('the_content', 'sproduct_display_form_on_single');

// Enqueue Frontend Scripts and Styles
function sproduct_enqueue_frontend_assets() {
    if (is_singular('sproduct')) {
        wp_enqueue_style('sproduct-frontend-css', SPRODUCT_URL . 'assets/frontend.css');
        wp_enqueue_script('sproduct-frontend-js', SPRODUCT_URL . 'assets/frontend.js', ['jquery'], null, true);
        
        // Pass AJAX URL and nonce to JavaScript
        wp_localize_script('sproduct-frontend-js', 'sproductAjax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('sproduct_form_nonce')
        ]);
    }
}
add_action('wp_enqueue_scripts', 'sproduct_enqueue_frontend_assets');
