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
    $textInputs = ['text','email','number','datepicker','nationalcode','postcode'];
    echo '<div class="sproductStepContainer">';
    foreach($form_data as $step){
        echo '<pre>';
        print_r($step);
        echo '</pre>';
        ?>
        <div class="sproductSingleStep">
            <div class="sproductStepHeader"><h3><?php echo $step['name'] ?></h3></div>
            <div class="sproductStepBody">
            <?php foreach($step['inputs'] as $input){ ?>
                <?php if(in_array($input['type'],$textInputs) ){ ?>
                    <label class="sproductSingleInput"><?php echo $input['name'] ?><input type="text" class="sproduct-input-<?php echo $input['width'] ?>" data-input-type="<?php echo $input['type'] ?>" data-input-minchar="<?php echo $input['logics'][0]['minchar'] ?>" data-input-maxchar="<?php echo $input['logics'][0]['maxchar'] ?>" data-input-minnum="<?php echo $input['logics'][0]['minnum'] ?>" data-input-maxnum="<?php echo $input['logics'][0]['maxnum'] ?>" data-input-required="<?php echo $input['required'] ? '1':'0' ?>" placeholder="<?php echo $input['placeholder'] ?>"></label>
                <?php } else{ ?>
                    <label class="sproductSingleInput anotherInputType"><?php echo $input['name'] ?><input type="text" class="sproduct-input-<?php echo $input['width'] ?>" data-input-type="<?php echo $input['type'] ?>" data-input-minchar="<?php echo $input['logics'][0]['minchar'] ?>" data-input-maxchar="<?php echo $input['logics'][0]['maxchar'] ?>" data-input-minnum="<?php echo $input['logics'][0]['minnum'] ?>" data-input-maxnum="<?php echo $input['logics'][0]['maxnum'] ?>" data-input-required="<?php echo $input['required'] ? '1':'0' ?>" placeholder="<?php echo $input['placeholder'] ?>"></label>
                <?php } ?>
            <?php } ?>
            </div>
        </div>
    <?php } 
    echo '</div>';
    ob_start();
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

