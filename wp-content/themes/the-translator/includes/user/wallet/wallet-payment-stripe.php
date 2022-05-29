<?php
/*
    * current-php-code 2020-Feb-25
    * input-sanitized :
    * current-wp-template:  for translator and customer wallet
*/
?>
<!--For Stripe credit card box -->
<link rel="stylesheet" href="<?= get_template_directory_uri() . '/includes/user/wallet/wallet-payment-stripe.css' ?>"/>
<div style="display: none">
    <div class="fl-stripe-form-source">
        <div class="sr-root">

            <div class="sr-main">
                <h3>Refill Using Card</h3>
                <form class="sr-payment-form">
                    <div class="sr-combo-inputs-row">
                        <div class="sr-input sr-card-element card-element"></div>
                    </div>
                    <div class="sr-field-error" id="card-errors" role="alert"></div>
                    <button>
                        <span class="spinner hidden" id="spinner"></span>
                        <span id="button-text">Pay <span class="order-amount"></span> </span>
                    </button>
                </form>
                <div class="sr-result hidden">
                    <p>Payment completed<br/></p>
                    <pre>
            <code></code>
          </pre>
                </div>
            </div>
        </div>
    </div>
</div>


