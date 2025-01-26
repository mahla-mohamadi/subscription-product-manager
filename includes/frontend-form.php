<?php
// Inject Form into Single sproduct Posts
function sproduct_display_form_on_single($content)
{
    if (is_singular('sproduct') && in_the_loop() && is_main_query()) {
        global $post;
        $form_data = get_post_meta($post->ID, '_sproduct_form_data', true);
        if (!$form_data) {
            return $content . '<p>فرم اشتراک یافت نشد.</p>';
        }
    }
    $form_data = json_decode($form_data, true);
    $plans = get_post_meta($post->ID, '_sproduct_plans', true);
    $plans = maybe_unserialize($plans);  // Ensure proper decoding
    $textInputs = ['text','email','number','nationalcode','postcode','datepicker'];
    ob_start();
    echo '<div class="sproductStepContainer">';
    foreach($form_data as $step){
        // echo '<pre>';
        // print_r($step);
        // echo '</pre>';
        ?>
        <div class="sproductSingleStep">
            <div class="sproductStepHeader"><h3><?php echo $step['name'] ?></h3></div>
            <div class="sproductStepBody">
            <?php foreach($step['inputs'] as $input){ ?>
                <?php if(in_array($input['type'],$textInputs) ){ ?>
                    <div class="sproductSingleInput sproduct-wrapper-<?php echo $input['width'] ?>"><span class="sproductLabel"><?php echo $input['name'] ?></span><input type="text" class="sproduct-input sproduct-input-<?php echo $input['width'] ?>" data-form-name="<?php echo $input['name'] ?>" name="<?php echo $input['name'] ?>" data-input-type="<?php echo $input['type'] ?>" data-input-minchar="<?php echo $input['logics'][0]['minchar'] ?>" data-input-maxchar="<?php echo $input['logics'][0]['maxchar'] ?>" data-input-minnum="<?php echo $input['logics'][0]['minnum'] ?>" data-input-maxnum="<?php echo $input['logics'][0]['maxnum'] ?>" data-input-required="<?php echo $input['isRequired'] ? '1':'0' ?>" placeholder="<?php echo $input['placeholder'] ?>" <?php echo ($input['type']=='datepicker') ? 'data-jdp data-jdp-only-date':'' ?>></div>
                <?php } elseif($input['type']=='radio'){ ?>
                    <div class="sproductSingleInput sproductSingleInputRadio sproduct-wrapper-<?php echo $input['width'] ?><?php echo $input['isVertical'] ? ' sproduct-vertical':'' ?>">
                        <?php echo $input['name'] ?>
                        <?php foreach($input['options'] as $option){ ?>
                        <label><input type="radio" class="sproduct-input sproduct-input-<?php echo $input['width'] ?>" data-form-name="<?php echo $input['name'] ?>" name="<?php echo $input['name'] ?>" data-input-type="<?php echo $input['type'] ?>" data-input-required="<?php echo $input['isRequired'] ? '1':'0' ?>" value="<?php echo $option['name'] ?>"><?php echo $option['name'] ?></label>
                        <?php } ?>
                    </div>
                <?php } elseif($input['type']=='checkbox'){ ?>
                    <div class="sproductSingleInput sproductSingleInputCheckbox sproduct-wrapper-<?php echo $input['width'] ?><?php echo $input['isVertical'] ? ' sproduct-vertical':'' ?>">
                        <?php echo $input['name'] ?>
                        <?php foreach($input['options'] as $option){ ?>
                        <label><input type="checkbox" class="sproduct-input sproduct-input-<?php echo $input['width'] ?>" data-form-name="<?php echo $input['name'] ?>" name="<?php echo $input['name'] ?>" data-input-type="<?php echo $input['type'] ?>" data-input-required="<?php echo $input['isRequired'] ? '1':'0' ?>" value="<?php echo $option['name'] ?>"><?php echo $option['name'] ?></label>
                        <?php } ?>
                    </div>
                <?php } elseif($input['type']=='textarea'){ ?>
                    <div class="sproductSingleInput sproduct-wrapper-<?php echo $input['width'] ?>"><span class="sproductLabel"><?php echo $input['name'] ?></span><textarea class="sproduct-input sproduct-input-<?php echo $input['width'] ?>" data-form-name="<?php echo $input['name'] ?>" name="<?php echo $input['name'] ?>" data-input-type="<?php echo $input['type'] ?>" data-input-required="<?php echo $input['isRequired'] ? '1':'0' ?>" placeholder="<?php echo $input['placeholder'] ?>"></textarea></div>
                <?php } elseif($input['type']=='file'){ ?>
                    <div class="sproductSingleInput sproduct-wrapper-<?php echo $input['width'] ?>"><span class="sproductLabel"><?php echo $input['name'] ?></span><input type="file" class="sproduct-input sproduct-input-file sproduct-input-<?php echo $input['width'] ?>" data-form-name="<?php echo $input['name'] ?>" name="<?php echo $input['name'] ?>" data-input-required="<?php echo $input['isRequired'] ? '1':'0' ?>" placeholder="<?php echo $input['placeholder'] ?>"><div class="sproduct-input-file-button">بارگذاری</button></div>
                <?php } ?>
                
            <?php } ?>
            </div>
        </div>
    <?php } ?>
    </div>
    <div class="sproductPlanStep">
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
    <?php
        echo $post->ID;
        $linkedProductArgs = ['post_type' => 'product', 'post_status' => 'publish','posts_per_page' => -1,'meta_query' => [['key' => '_selected_sproduct_ids','value' => 'i:' . $post->ID . ';','compare' => 'LIKE']]];
        $linkedProductQuery = new WP_Query($linkedProductArgs);
        if ($linkedProductQuery->have_posts()){ ?>
            <div class="sproductLinkedProductStep">
                <h3>همراه با اشتراک سفارش دهید</h3>
                <div class="sproductLinkedProductBody">
                <?php while ($linkedProductQuery->have_posts()){ $linkedProductQuery->the_post(); ?>
                    <label>
                        <input type="checkbox" id="sproductLinkedProductCheckbox" class="sproductLinkedProductCheckbox" name="selected_products[]" value="<?php echo get_the_ID(); ?>">
                        <img src="<?php the_post_thumbnail_url() ?>" />
                        <?php the_title(); ?>
                    </label>
                <?php } ?>
                </div>
            </div>
        <?php } else{ ?>
            <p>هیچ محصولی با این اشتراک مرتبط نیست.</p>
        <?php } ?>
    <?php wp_reset_postdata(); ?>
    <div class="sproductFormNavigator">
        <div class="sproductFormButton sproductFormButtonPrev"><span class="sproductFormButtonIcon"><svg width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><g stroke-width="0"/><g stroke-linecap="round" stroke-linejoin="round"/><path fill="none" stroke="#3d3d3d" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8.5 5 7 7-7 7"/></svg></span><span>مرحله قبل</span></div>
        <div class="sproductFormButton sproductFormButtonNext"><span>مرحله بعد</span><span class="sproductFormButtonIcon"><svg width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><g stroke-width="0"/><g stroke-linecap="round" stroke-linejoin="round"/><path fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15.5 5-7 7 7 7"/></svg></span></div>
        <div class="sproductFormButton sproductFormButtonProceed"><span>ادامه</span><span class="sproductFormButtonIcon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g stroke-width="0"/><g stroke-linecap="round" stroke-linejoin="round"/><path d="m6 12 4.243 4.243 8.484-8.486" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span></div>
    </div>
    <?php
    $content = ob_get_clean();
    return $content;
}
add_filter('the_content', 'sproduct_display_form_on_single');

// Enqueue Frontend Scripts and Styles
function sproduct_enqueue_frontend_assets()
{
    if (is_singular('sproduct')) {
        wp_enqueue_style('sproduct-frontend-css', SPRODUCT_URL . 'assets/frontend.css');

        // wp_enqueue_style('persian-datepicker', SPRODUCT_URL . 'assets/persian-datepicker.css');
        // wp_enqueue_style('persian-datepicker-min', SPRODUCT_URL . 'assets/persian-datepicker.min.css');
        wp_enqueue_style('jalalidatepickercss', SPRODUCT_URL . 'assets/jalalidatepicker.min.css');
        wp_enqueue_script('jalalidatepickerjs', SPRODUCT_URL . 'assets/jalalidatepicker.min.js');
        wp_enqueue_script('sproduct-frontend-js', SPRODUCT_URL . 'assets/frontend.js', ['jquery'], null, true);

        // wp_enqueue_script('persian-date', SPRODUCT_URL . 'assets/persian-date.min.js', ['jquery'], null, true);
        // wp_enqueue_script('persian-datepicker', SPRODUCT_URL . 'assets/persian-datepicker.min.js', ['jquery'], null, true);

        // Pass AJAX URL and nonce to JavaScript
        wp_localize_script('sproduct-frontend-js', 'sproductAjax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('sproduct_form_nonce')
        ]);
    }
}
add_action('wp_enqueue_scripts', 'sproduct_enqueue_frontend_assets');

