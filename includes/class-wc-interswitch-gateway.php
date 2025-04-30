<?php
class WC_Interswitch_Gateway extends WC_Payment_Gateway {
    public function __construct() {
        $this->id = 'interswitch';
        $this->icon = plugins_url('assets/images/interswitch-logo.svg', dirname(__FILE__));
        $this->has_fields = false;
        $this->method_title = 'Interswitch Payment Gateway';
        $this->method_description = 'Accept payments through Interswitch payment gateway';

        // Load settings
        $this->init_form_fields();
        $this->init_settings();

        // Define properties
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->merchant_code = $this->get_option('merchant_code');
        $this->domain = $this->get_option('domain');
        $this->currency_code = $this->get_option('currency_code');
        $this->enabled = $this->get_option('enabled');

        // Actions
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_api_wc_interswitch_gateway', array($this, 'check_ipg_response'));
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => 'Enable/Disable',
                'type' => 'checkbox',
                'label' => 'Enable Interswitch Payment Gateway',
                'default' => 'no'
            ),
            'title' => array(
                'title' => 'Title',
                'type' => 'text',
                'description' => 'Payment method title that customers will see',
                'default' => 'Interswitch Payment',
                'desc_tip' => true,
            ),
            'description' => array(
                'title' => 'Description',
                'type' => 'textarea',
                'description' => 'Payment method description that customers will see',
                'default' => 'Pay securely using Interswitch payment gateway',
            ),
            'merchant_code' => array(
                'title' => 'Merchant Code',
                'type' => 'text',
                'description' => 'Your Interswitch merchant code',
            ),
            'domain' => array(
                'title' => 'Domain',
                'type' => 'text',
                'description' => 'Your Interswitch domain (e.g., ISWUG)',
                'default' => 'ISWUG'
            ),
            'currency_code' => array(
                'title' => 'Currency Code',
                'type' => 'text',
                'description' => 'Currency code (e.g., UGX)',
                'default' => 'UGX'
            )
        );
    }

    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        
        $checkout_data = array(
            'transactionReference' => 'WC-' . $order_id . '-' . time(),
            'orderId' => $order_id,
            'amount' => $order->get_total() * 100, // Convert to cents
            'dateOfPayment' => date('c'),
            'redirectUrl' => home_url('/wc-api/wc_interswitch_gateway'),
            'narration' => 'Order payment #' . $order_id,
            'expiryTime' => date('c', strtotime('+1 hour')),
            'customerId' => $order->get_customer_id(),
            'customerFirstName' => $order->get_billing_first_name(),
            'customerSecondName' => $order->get_billing_last_name(),
            'customerEmail' => $order->get_billing_email(),
            'customerMobile' => $order->get_billing_phone(),
            'merchantCode' => $this->merchant_code,
            'terminalType' => 'WEB',
            'domain' => $this->domain,
            'currencyCode' => $this->currency_code,
            'fee' => '0',
            'merchantName' => get_bloginfo('name'),
            'customerCity' => $order->get_billing_city(),
            'customerCountry' => $order->get_billing_country(),
            'customerState' => $order->get_billing_state(),
        );

        // Store transaction data in order meta
        update_post_meta($order_id, '_interswitch_checkout_data', $checkout_data);

        // If this is from blocks checkout or regular checkout with AJAX
        if (defined('REST_REQUEST') && REST_REQUEST) {
            return [
                'result' => 'success',
                'redirect' => add_query_arg('order_pay_ipg', $order_id, $order->get_checkout_payment_url(true))
            ];
        }

        // Load and display the payment form template
        ob_start();
        include(plugin_dir_path(dirname(__FILE__)) . 'templates/payment-form.php');
        $html = ob_get_clean();

        return array(
            'result' => 'success',
            'redirect' => false,
            'html' => $html
        );
    }

    public function is_available() {
        // Check if the gateway is enabled
        $is_available = ('yes' === $this->enabled);

        // Log the availability status
        error_log('Interswitch Gateway is available: ' . ($is_available ? 'Yes' : 'No'));

        return $is_available;
    }
}