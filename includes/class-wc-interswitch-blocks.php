<?php
/**
 * Interswitch Payment Blocks Class
 */
class WC_Interswitch_Blocks {
    /**
     * Initialize blocks integration
     */
    public function __construct() {
        add_action('woocommerce_blocks_loaded', array($this, 'init'));
    }

    /**
     * Register payment method integration
     */
    public function init() {
        if (!class_exists('Automattic\WooCommerce\Blocks\Payments\Integration')) {
            return;
        }

        add_action('woocommerce_blocks_payment_method_type_registration', function($registry) {
            $registry->register(new WC_Interswitch_Blocks_Support());
        });
    }
}

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class WC_Interswitch_Blocks_Support extends AbstractPaymentMethodType {
    protected $name = 'interswitch';
    private $gateway;

    public function initialize() {
        $this->settings = get_option('woocommerce_interswitch_settings', []);
        $this->gateway = new WC_Interswitch_Gateway();
    }

    public function is_active() {
        return $this->gateway->is_available();
    }

    public function get_payment_method_script_handles() {
        wp_register_script(
            'wc-interswitch-blocks',
            plugins_url('assets/js/blocks.js', dirname(__FILE__)),
            [
                'wc-blocks-registry',
                'wc-settings',
                'wp-element',
                'wp-html-entities',
                'wp-i18n',
            ],
            null,
            true
        );
        
        if (function_exists('wp_set_script_translations')) {
            wp_set_script_translations('wc-interswitch-blocks');
        }
        
        return ['wc-interswitch-blocks'];
    }

    public function get_payment_method_data() {
        return [
            'title' => $this->settings['title'] ?? 'Interswitch Payment Gateway',
            'description' => $this->settings['description'] ?? 'Pay securely via Interswitch payment gateway',
            'supports' => [
                'products',
                'refunds'
            ],
            'icon' => plugins_url('assets/images/interswitch-logo.svg', dirname(__FILE__))
        ];
    }
}

new WC_Interswitch_Blocks();