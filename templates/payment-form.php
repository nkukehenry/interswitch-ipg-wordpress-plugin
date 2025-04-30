<?php
if (!defined('ABSPATH')) {
    exit;
}

$order_id = get_query_var('order_pay_ipg');
$order = wc_get_order($order_id);
$checkout_data = get_post_meta($order_id, '_interswitch_checkout_data', true);
?>

<style>
.interswitch-payment-form {
    max-width: 600px;
    margin: 2em auto;
    padding: 2em;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.interswitch-logo {
    max-width: 200px;
    margin: 0 auto 2em;
    height: auto;
}

.payment-info {
    margin: 1.5em 0;
    padding: 1em;
    background: #f8f9fa;
    border-radius: 4px;
}

.payment-amount {
    font-size: 1.5em;
    font-weight: bold;
    color: #333;
    margin: 0.5em 0;
}

.loading-spinner {
    display: none;
    margin: 1em auto;
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.payment-status {
    margin-top: 1em;
    font-size: 1.1em;
    color: #666;
}
</style>

<div class="interswitch-payment-form">
    <img src="<?php echo plugins_url('assets/images/interswitch-logo.svg', dirname(__FILE__)); ?>" alt="Interswitch" class="interswitch-logo" />
    
    <div class="payment-info">
        <h2>Order Payment</h2>
        <div class="payment-amount">
            <?php echo get_woocommerce_currency_symbol() . $order->get_total(); ?>
        </div>
        <p>Order #<?php echo $order->get_order_number(); ?></p>
    </div>

    <div class="loading-spinner"></div>
    <div class="payment-status">Initializing secure payment...</div>

    <script>
        const checkoutData = <?php echo json_encode($checkout_data); ?>;

        function checkout(jsonData) {
            const checkoutForm = document.createElement("form");
            checkoutForm.style.display = "none";
            checkoutForm.method = "POST";
            checkoutForm.action = "https://gatewaybackend.quickteller.co.ke/ipg-backend/api/checkout";

            for (const key in jsonData) {
                const formField = document.createElement("input");
                formField.name = key;
                formField.value = jsonData[key];
                checkoutForm.appendChild(formField);
            }

            document.body.appendChild(checkoutForm);
            
            // Show loading state
            document.querySelector('.loading-spinner').style.display = 'block';
            document.querySelector('.payment-status').textContent = 'Redirecting to secure payment page...';
            
            setTimeout(() => {
                checkoutForm.submit();
                document.body.removeChild(checkoutForm);
            }, 1000); // Small delay to show the loading state
        }

        function payNow() {
            checkout(checkoutData);
        }

        // Auto-submit the form
        document.addEventListener("DOMContentLoaded", payNow);
    </script>
</div>