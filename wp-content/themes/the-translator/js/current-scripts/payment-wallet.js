
var formatter  = null;
function fl_show_amount_with_fee(amount,dom_element_we_write_to,processing_fee_text) {

    var data = { 'action': 'fl_get_refill_processing_fee','amount': amount };

    jQuery.post( adminAjax.url, data, function(response_raw){

        /**
         * @type {FreelinguistAmountWithFeeResponse} response
         */
        var response = freelinguist_safe_cast_to_object(response_raw);
        if (response.status === true) {
            let formatted_total = formatter.format(response.total);
            let formatted_fee = formatter.format(response.processing_fee);
            dom_element_we_write_to.innerHTML = processing_fee_text + ": " + formatted_fee +
                "<span style='display: block; margin-top: 0.5em'>" + "Total Amount: " + formatted_total + "</span>";
            jQuery('#amount').data('fullamount',response.total)
        } else {
            will_handle_ajax_error('Show Amount With Fee',response.message);

        }


    },'json');
}


function start_stripe_alipay_payment(amount) {
    const orderData = new FormData();

    orderData.append( 'action', 'fl_stripe_create_payment_intent' );
    orderData.append( 'currency', 'usd' );
    orderData.append( 'source', 'alipay' );
    orderData.append( 'amount', amount );
    orderData.append( 'items', JSON.stringify([{id: "refill"}] ));
    orderData.append( 'lang', 'en' );

    var stripe;

    fetch(adminAjax.url, {
        method: "POST",
        credentials: 'same-origin',
        body: orderData
    })
        .then(function (result) {
            return result.json();
        })
        .then(function (data) {
            console.debug('got result back from creating payment intent',data);
            do_alipay(data);
        })
        .catch((error) => {
            console.log('[Stripe Payment AliPay Setup]');
            console.error(error);
    });

    /**
     *
     * @param {FreelinguistCreatePaymentIntent} data
     */
    function do_alipay(data) {
        stripe = Stripe(data.publishable_key);

        // noinspection JSUnresolvedFunction
        stripe.confirmAlipayPayment(data.client_secret, {
            // Return URL where the customer should be redirected to after payment
            return_url: data.return_url,
        }).then((result) => {
            console.debug('Got result back from Ali Pay',result);
            if (result.error) {
                // Inform the customer that there was an error.
                will_handle_ajax_error('AliPay Payment Setup',result.error.message);
            }
        });
    }
}

function start_stripe_credit_card_payment(amount) {
// A reference to Stripe.js
    var stripe;
    const orderData = new FormData();

    orderData.append( 'action', 'fl_stripe_create_payment_intent' );
    orderData.append( 'currency', 'usd' );
    orderData.append( 'amount', amount );
    orderData.append( 'items', JSON.stringify([{id: "refill"}] ));
    orderData.append( 'lang', 'en' );

// Disable the button until we have Stripe set up on the page
    document.querySelector("button").disabled = true;

    fetch(adminAjax.url, {
        method: "POST",
        credentials: 'same-origin',
        body: orderData
    })
        .then(function (result) {
            return result.json();
        })
        .then(function (data) {
            return setupElements(data);
        })
        .then(function ({stripe, card, clientSecret}) {
            document.querySelector("button").disabled = false;

            // Handle form submission.
            jQuery(".freelinguist-card-element-holder form.sr-payment-form").submit(function(event){
                event.preventDefault();
                // Initiate payment when the submit button is clicked
                pay(stripe, card, clientSecret);
            });

        }).catch((error) => {
            console.log('[Stripe Payment Card Setup]');
            console.error(error);
    });

// Set up Stripe.js and Elements to use in checkout form

    /**
     *
     * @param {FreelinguistCreatePaymentIntent} data
     * @returns {{stripe: stripe.Stripe | *, card: stripe.elements.Element, clientSecret: string}}
     */
    var setupElements = function (data) {
        //
        stripe = Stripe(data.publishable_key);
        var elements = stripe.elements();
        var style = {
            base: {
                color: "#32325d",
                fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                fontSmoothing: "antialiased",
                fontSize: "16px",
                "::placeholder": {
                    color: "#aab7c4"
                }
            },
            invalid: {
                color: "#fa755a",
                iconColor: "#fa755a"
            }
        };

        var card = elements.create("card", {style: style});
        card.mount(".freelinguist-card-element-holder .card-element");
        return {
            stripe: stripe,
            card: card,
            clientSecret: data.client_secret
        };
    };

    /*
     * Calls stripe.confirmCardPayment which creates a pop-up modal to
     * prompt the user to enter extra authentication details without leaving your page
     */
    var pay = function (stripe, card, clientSecret) {
        changeLoadingState(true);

        // Initiate the payment.
        // If authentication is required, confirmCardPayment will automatically display a modal
        var what  = stripe
            .confirmCardPayment(clientSecret, {payment_method: {card: card}})
            .then(function (result) {
                if (result.error) {
                    // Show error to your customer
                    showError(result.error.message);
                } else {
                    // The payment has been processed!
                    orderComplete(clientSecret);
                }
            }).catch((error) => {
                console.log('[Stripe Payment Setup]');
                console.error(error);
            });
        console.log(what);
    };

    /* ------- Post-payment helpers ------- */

    /* Shows a success / error message when the payment is complete */
    var orderComplete = function (clientSecret) {
        stripe.retrievePaymentIntent(clientSecret).then(function (result) {
            var paymentIntent = result.paymentIntent;

            var paymentIntentJson = JSON.stringify(paymentIntent, null, 2);
                //task-future-work show more of a confirmation message here
            document.querySelector(".sr-payment-form").classList.add("hidden");
            document.querySelector("pre").textContent = paymentIntentJson;

            document.querySelector(".sr-result").classList.remove("hidden");
            setTimeout(function () {
                document.querySelector(".sr-result").classList.add("expand");
                window.location.reload(true);
            }, 200);

            changeLoadingState(false);
        });
    };

    var showError = function (errorMsgText) {
        changeLoadingState(false);
        var errorMsg = document.querySelector(".sr-field-error");
        errorMsg.textContent = errorMsgText;
        setTimeout(function () {
            errorMsg.textContent = "";
        }, 4000);
    };

// Show a spinner on payment submission
    var changeLoadingState = function (isLoading) {
        if (isLoading) {
            document.querySelector("button").disabled = true;
            document.querySelector("#spinner").classList.remove("hidden");
            document.querySelector("#button-text").classList.add("hidden");
        } else {
            document.querySelector("button").disabled = false;
            document.querySelector("#spinner").classList.add("hidden");
            document.querySelector("#button-text").classList.remove("hidden");
        }
    };

}

function do_the_buttons() {
    var payment_method = jQuery('[name="payment_notify"]:checked').val();
    if (payment_method === 'credit_card_stripe') {
        jQuery('#button-begin-paypal-refill').css('display', 'none'); //payPal button hide
        jQuery('#button-begin-stripe-refill').css('display', 'inline').addClass('btn blue-btn pay-refill');
        jQuery('#button-begin-alipay-refill').css('display', 'none');   // AliPay button hide
    } else if (payment_method === 'alipay') {
        jQuery('#button-begin-paypal-refill').css('display', 'none'); //PayPal button hide
        jQuery('#button-begin-alipay-refill').css('display', 'inline').addClass('btn blue-btn pay-refill');
        jQuery('#button-begin-stripe-refill').css('display', 'none');   // Stripe button hide
    } else if (payment_method === 'paypal') {
        jQuery('#button-begin-paypal-refill').css('display', 'inline');
        jQuery('#button-begin-stripe-refill').css('display', 'none');   // Stripe button hide
        jQuery('#button-begin-alipay-refill').css('display', 'none');   // AliPay button hide
    }else {
        jQuery('#button-begin-paypal-refill').css('display', 'none');   // paypal button hide
        jQuery('#button-begin-stripe-refill').css('display', 'none');   // Stripe button hide
        jQuery('#button-begin-alipay-refill').css('display', 'none');   // AliPay button hide
    }
}



jQuery(function ($) {
    var modal;
    let b_initialize_stripe = true;
    let f_rem_amount = null;
    // Create our number formatter.
    formatter = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD', //code-notes read denomination later

        // These options are needed to round to whole numbers if that's what you want.
        //minimumFractionDigits: 0, // (this suffices for whole numbers, but will print 2500.10 as $2,500.1)
        //maximumFractionDigits: 0, // (causes 2500.99 to be printed as $2,501)
    });

    do_the_buttons();

    jQuery('#amount').change(function () {
        do_the_buttons();
    });

    jQuery('input[type=radio][name=payment_notify]').change(function () {
        do_the_buttons();
    });


    //code-bookmark when the button for AliPay payment is clicked
    jQuery('#button-begin-alipay-refill').click(function() {
        let amount = parseFloat(jQuery('#amount').data('fullamount'));
        start_stripe_alipay_payment(amount);
    });

    //code-bookmark when the button for stripe payment is clicked
    jQuery('#button-begin-stripe-refill').click(function() {
        let amount = parseFloat(jQuery('#amount').data('fullamount'));

        let formatted_amount = formatter.format(amount);

        $('div.fl-stripe-form-source  .order-amount').text(formatted_amount);

        let things_to_show = $('div.fl-stripe-form-source')[0];



        if(modal) {
            b_initialize_stripe = f_rem_amount !== amount;

        } else {
            // noinspection JSPotentiallyInvalidConstructorUsage
            modal = new tingle.modal({
                footer: true,
                stickyFooter: true,
                closeMethods: ['overlay', 'button', 'escape'],
                closeLabel: "Close",
                cssClass: ['freelinguist-card-element-holder'],
                onOpen: function () {
                    //console.log('modal open');
                    if (b_initialize_stripe) {
                        let amount = parseFloat(jQuery('#amount').data('fullamount'));
                        console.log('reinitializing',amount);
                        start_stripe_credit_card_payment(amount);
                    }

                },
                onClose: function () {
                    // console.log('modal closed');
                },
                beforeClose: function () {
                    // here's goes some logic
                    // e.g. save content before closing the modal
                    return true; // close the modal
                    //return false; // nothing happens
                }
            });

            // set content
            modal.setContent(things_to_show);

            // add a button
            modal.addFooterBtn('Close', 'tingle-btn tingle-btn--primary tingle-btn--default', function() {
                // here goes some logic
                modal.close();
            });
        }
        f_rem_amount = amount;


        // open modal
        modal.open();

    })
});

jQuery(function($){
    $(".fl-wallet.a-timestamp-full-date-time").each(function () {
        var qthis = $(this);
        var ts = $(this).data('ts');
        if (ts === 0 || ts === '0' || ts === undefined || ts === '') {
            qthis.text('');
        } else {
            var ts_number = parseInt(ts.toString());
            var m = moment(ts_number * 1000);
            qthis.text(m.format('MMM D YYYY H:mm'));
        }
    });
});
