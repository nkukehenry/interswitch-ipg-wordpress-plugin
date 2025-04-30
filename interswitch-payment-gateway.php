<?php
/*
  Plugin Name: Interswitch Payment Gateway
  Plugin URI: https://interswitchgroup.com/payment-gateway
  Description: Accept payments via Interswitch Payment Gateway - Supporting multiple payment methods including cards, mobile money, and bank transfers across East Africa
  Version: 1.0.0
  Author: Henry Nkuke
  Author URI: https://interswitchgroup.com
  License: GPLv2
  License URI: https://www.gnu.org/licenses/gpl-2.0.html
  WC requires at least: 8.0.0
  WC tested up to: 8.6.0
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Initialize the gateway when plugins are loaded
add_action('plugins_loaded', 'init_interswitch_gateway', 0);

function init_interswitch_gateway() {
    if (!class_exists('WC_Payment_Gateway')) {
        return; // Exit if WooCommerce is not active
    }

    // Include the gateway class
    require_once plugin_dir_path(__FILE__) . 'includes/class-wc-interswitch-gateway.php';
    
    // Add the gateway to WooCommerce
    add_filter('woocommerce_payment_gateways', 'add_interswitch_gateway');
}

function add_interswitch_gateway($methods) {
    $methods[] = 'WC_Interswitch_Gateway';
    return $methods;
}

/**
 * Declare compatibility with cart checkout blocks feature
 */
function declare_interswitch_cart_checkout_blocks_compatibility() {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, false);
    }
}
add_action('before_woocommerce_init', 'declare_interswitch_cart_checkout_blocks_compatibility');

/**
 * Register Interswitch payment method for blocks checkout
 */
function register_interswitch_blocks_payment_method() {
    if (!class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
        return;
    }

    // Include the blocks support class
    require_once plugin_dir_path(__FILE__) . 'includes/class-wc-interswitch-blocks.php';
    
    // Register the payment method type
    add_action(
        'woocommerce_blocks_payment_method_type_registration',
        function (Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
            $payment_method_registry->register(new WC_Interswitch_Blocks_Support());
        }
    );
}
add_action('woocommerce_blocks_loaded', 'register_interswitch_blocks_payment_method');

/**
 * Add custom query vars
 */
function add_interswitch_query_vars($vars) {
    $vars[] = 'order_pay_ipg';
    return $vars;
}
add_filter('query_vars', 'add_interswitch_query_vars');

/**
 * Handle payment form template display
 */
function handle_interswitch_payment_template() {
    $order_pay_ipg = get_query_var('order_pay_ipg');
    
    if ($order_pay_ipg) {
        include plugin_dir_path(__FILE__) . 'templates/payment-form.php';
        exit;
    }
}
add_action('template_redirect', 'handle_interswitch_payment_template');

// Include response handler
require_once plugin_dir_path(__FILE__) . 'includes/class-wc-interswitch-response-handler.php';