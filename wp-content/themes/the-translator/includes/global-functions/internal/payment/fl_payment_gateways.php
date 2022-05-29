<?php

class FLPaymentGateways {

    const GATEWAY_STRIPE_CREDIT_CARD = FLTransactionLookup::PAYMENT_TYPE_VALUES[FLTransactionLookup::PAYMENT_TYPE_STRIPE];
    const GATEWAY_PAYPAL = FLTransactionLookup::PAYMENT_TYPE_VALUES[FLTransactionLookup::PAYMENT_TYPE_PAYPAL];
    const GATEWAY_ALIPAY = FLTransactionLookup::PAYMENT_TYPE_VALUES[FLTransactionLookup::PAYMENT_TYPE_ALIPAY];

    const DEFAULT_WITHDRAW_GATEWAY_NAMES_ARRAY = [
        FLPaymentGateways::GATEWAY_PAYPAL,
        FLPaymentGateways::GATEWAY_ALIPAY,
    ];

    const DEFAULT_PAYMENT_GATEWAY_NAMES_ARRAY = [
        FLPaymentGateways::GATEWAY_STRIPE_CREDIT_CARD,
        FLPaymentGateways::GATEWAY_PAYPAL,
        FLPaymentGateways::GATEWAY_ALIPAY,
    ];

    const OPTION_NAME_ENABLED_PAYMENT_GATEWAYS = 'fl_payment_gateways_array_of_names';
    const OPTION_NAME_ENABLED_WITHDRAW_GATEWAYS = 'fl_withdraw_gateways_array_of_names';
    const OPTION_NAME_DEFAULT_WITHDRAW_METHOD = 'default_withdraw_method';
    const OPTION_NAME_DEFAULT_PAYMENT_METHOD = 'default_payment_method';
}