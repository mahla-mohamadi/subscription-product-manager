<?php
// Inject Form into Single sproduct Posts
function sproduct_display_form_on_single($content)
{
    if (is_singular('sproduct') && in_the_loop() && is_main_query()) {
        global $post;
        $form_data = get_post_meta($post->ID, '_sproduct_form_data', true);
        if (!$form_data) {
            return $content . '<p>No form data found.</p>';
        }
        $form_data = json_decode($form_data, true);

        $plans = get_post_meta($post->ID, '_sproduct_plans', true);
        $plans = maybe_unserialize($plans);  // Ensure proper decoding
        ob_start();
        ?>
        <div id="sproduct-form-frontend" data-post-id="<?php echo esc_attr($post->ID); ?>">
            <form id="sproduct-main-form" method="POST">
                <?php foreach ($form_data as $step_index => $step): ?>
                    <div class="sproduct-step" data-step="<?php echo $step_index; ?>" <?php echo $step_index !== 0 ? 'style="display:none;"' : ''; ?>>

                        <h3><?php echo esc_html($step['name']); ?></h3>
                        <?php foreach ($step['inputs'] as $input_index => $input):  ?>

                            <div class="sproduct-input <?php echo $input['required'] ? 'is_required' : ''; ?><?php echo !empty($input['vertical']) ? 'verticalSelected' : ''; ?> 
                                        <?php echo !empty($input['horizontal']) ? 'horizontalSelected' : ''; ?>">

                                <label><?php echo esc_html($input['label']); ?></label>
                                <?php if ($input['type'] === 'checkbox_group'): ?>
                                    <div class="checkbox-group">
                                        <?php foreach ($input['options'] as $option_index => $option): ?>
                                            <div>
                                                <label>
                                                    <input type="checkbox"
                                                        name="sproduct_input_<?php echo $step_index; ?>_<?php echo $input_index; ?>[]"
                                                        value="<?php echo esc_attr($option); ?> ">
                                                    <?php echo esc_html($option); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($input['type'] === 'radio_group'): ?>
                                    <div class="radio-group">
                                        <?php foreach ($input['options'] as $option_index => $option): ?>
                                            <div>
                                                <label>
                                                    <input type="radio"
                                                        name="sproduct_input_<?php echo $step_index; ?>_<?php echo $input_index; ?>"
                                                        value="<?php echo esc_attr($option); ?>">
                                                    <?php echo esc_html($option); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($input['type'] === 'text'): ?>
                                    <input type="text" name="sproduct_input_<?php echo $step_index; ?>_<?php echo $input_index; ?>"
                                        placeholder="<?php echo esc_attr($input['placeholder'] ?? ''); ?>" />

                                <?php elseif ($input['type'] === 'national_code' || $input['type'] === 'post_code'): ?>
                                    <input type="nationalcode" name="sproduct_input_<?php echo $step_index; ?>_<?php echo $input_index; ?>"
                                        placeholder="<?php echo esc_attr($input['placeholder'] ?? ''); ?>" />
                                <?php elseif ($input['type'] === 'date'): ?>
                                    <input type="text" id="<?php echo esc_attr($input['id']); ?>" class="datepicker-input"
                                        name="sproduct_input_<?php echo $step_index; ?>_<?php echo $input_index; ?>"
                                        placeholder="<?php echo esc_attr($input['placeholder'] ?? 'Select a date'); ?>" />
                                <?php elseif ($input['type'] === 'mobile'): ?>
                                    <input type="tel" name="sproduct_input_<?php echo $step_index; ?>_<?php echo $input_index; ?>"
                                        placeholder="<?php echo esc_attr($input['placeholder'] ?? ''); ?>" />

                                <?php elseif ($input['type'] === 'telephone'): ?>
                                    <input type="telephone" name="sproduct_input_<?php echo $step_index; ?>_<?php echo $input_index; ?>"
                                        placeholder="<?php echo esc_attr($input['placeholder'] ?? ''); ?>" />

                                <?php elseif ($input['type'] === 'textarea'): ?>
                                    <textarea name="sproduct_input_<?php echo $step_index; ?>_<?php echo $input_index; ?>"
                                        required="<?php echo $input['required'] ? 'required' : ''; ?>"
                                        placeholder="<?php echo esc_attr($input['placeholder'] ?? ''); ?>"></textarea>

                                <?php elseif ($input['type'] === 'email'): ?>
                                    <input type="email" name="sproduct_input_<?php echo $step_index; ?>_<?php echo $input_index; ?>"
                                        placeholder="<?php echo esc_attr($input['placeholder'] ?? ''); ?>" />
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>

                        <!-- Display Plans Only in the Final Step -->
                        <?php if ($step_index === count($form_data) - 1 && !empty($plans)): ?>
                            <div class="sproduct-plans">
                                <h3>پلن اشتراک خود را انتخاب کنید :</h3>
                                <?php foreach ($plans as $index => $plan): ?>
                                    <div class="plan-option">
                                        <input type="radio" id="plan_<?php echo $index; ?>" name="selected_plan"
                                            value="<?php echo esc_attr($plan['name']); ?>"
                                            data-plan-price="<?php echo esc_attr($plan['price']); ?>"
                                            data-plan-duration="<?php echo esc_attr($plan['days']); ?>" data-plan-is-trial="0" required>
                                        <label for="plan_<?php echo $index; ?>">
                                            <strong><?php echo esc_html($plan['name']); ?></strong>
                                            <?php echo esc_html($plan['days']); ?> روز -
                                            <?php echo esc_html($plan['price']); ?> تومان
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>




                        <?php endif; ?>

                        <?php if ($step_index === count($form_data) - 1 && !empty($plans)): ?>


                            <?php

                            $sproduct_id = get_the_ID();

                            $args = [
                                'post_type' => 'product',
                                'post_status' => 'publish',
                                'posts_per_page' => -1,
                                'meta_query' => [
                                    [
                                        'key' => '_selected_sproduct_ids',
                                        'value' => 'i:' . $sproduct_id . ';',
                                        'compare' => 'LIKE',
                                    ]
                                ]
                            ];

                            $query = new WP_Query($args);
                            ?>
                            <?php if ($query->have_posts()): ?>
                                <div class="productToSproductsDivParent">
                                    <h3>محصولاتی که این اشتراک را انتخاب کرده‌اند:</h3>
                                    <div class="productToSproductsDiv">
                                        <?php while ($query->have_posts()):
                                            $query->the_post(); ?>

                                            <label>
                                                <input type="checkbox" name="selected_products[]" value="<?php echo get_the_ID(); ?>">
                                                <img src="<?php the_post_thumbnail_url() ?>" />
                                                <?php the_title(); ?>
                                            </label>

                                        <?php endwhile; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <p>هیچ محصولی با این اشتراک مرتبط نیست.</p>
                            <?php endif; ?>

                            <?php wp_reset_postdata(); // بازنشانی کوئری ?>



                        <?php endif; ?>

                    </div>
                <?php endforeach; ?>
                <div class="sproduct-navigation">
                    <button type="button" id="prev-btn" disabled>قبلی</button>
                    <button type="button" id="next-btn">بعدی</button>
                    <button type="submit" id="submit-btn" style="display:none;">ارسال</button>
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
function sproduct_enqueue_frontend_assets()
{
    if (is_singular('sproduct')) {
        wp_enqueue_style('sproduct-frontend-css', SPRODUCT_URL . 'assets/frontend.css');

        wp_enqueue_style('persian-datepicker', SPRODUCT_URL . 'assets/persian-datepicker.css');
        wp_enqueue_style('persian-datepicker-min', SPRODUCT_URL . 'assets/persian-datepicker.min.css');

        wp_enqueue_script('sproduct-frontend-js', SPRODUCT_URL . 'assets/frontend.js', ['jquery'], null, true);

        wp_enqueue_script('persian-date', SPRODUCT_URL . 'assets/persian-date.min.js', ['jquery'], null, true);
        wp_enqueue_script('persian-datepicker', SPRODUCT_URL . 'assets/persian-datepicker.min.js', ['jquery'], null, true);

        // Pass AJAX URL and nonce to JavaScript
        wp_localize_script('sproduct-frontend-js', 'sproductAjax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('sproduct_form_nonce')
        ]);
    }
}
add_action('wp_enqueue_scripts', 'sproduct_enqueue_frontend_assets');

