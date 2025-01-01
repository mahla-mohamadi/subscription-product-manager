<?php
/**
 * Plugin Name: Subscription Products
 * Description: Custom Subscription Product Form with Dynamic Step-by-Step Form Builder.
 * Version: 1.0.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define Plugin Constants
define('SPRODUCT_PATH', plugin_dir_path(__FILE__));
define('SPRODUCT_URL', plugin_dir_url(__FILE__));

// Require Core Plugin Files
require_once SPRODUCT_PATH . 'includes/cpt.php';              // Custom Post Type
require_once SPRODUCT_PATH . 'includes/form-builder.php';     // Admin Form Builder
require_once SPRODUCT_PATH . 'includes/frontend-form.php';    // Frontend Form Display
require_once SPRODUCT_PATH . 'includes/ajax-handler.php';     // AJAX Handlers
require_once SPRODUCT_PATH . 'includes/db.php';               // Database Operations (Optional)

// Activation Hook (Create Tables on Activation)
register_activation_hook(__FILE__, 'sproduct_install');
function sproduct_install() {
    sproduct_create_db_tables();  // Database Table Creation
    flush_rewrite_rules();
}

// Deactivation Hook
register_deactivation_hook(__FILE__, 'sproduct_uninstall');
function sproduct_uninstall() {
    flush_rewrite_rules();
}


