<?php

return array(
    'encKey' => env('PAYMENT_ENC_KEY'),
    'decKey' => env('PAYMENT_DEC_KEY'),
    'payUrl' => env('PAYMENT_URL'),
    'transactionTrackingUrl' => env('PAYMENT_TRANSACTION_TRACKING_URL'),
    'merchantId' => env('PAYMENT_MERCHANT_ID'),
    'password' => env('PAYMENT_PAYMENT_PASSWORD')
);
