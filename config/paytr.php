<?php

return [
    'merchant_id'     => env('PAYTR_MERCHANT_ID', ''),
    'merchant_key'    => env('PAYTR_MERCHANT_KEY', ''),
    'merchant_salt'   => env('PAYTR_MERCHANT_SALT', ''),
    'test_mode'       => env('PAYTR_TEST_MODE', true),
    'debug_on'        => env('PAYTR_DEBUG', true),
    'no_installment'  => env('PAYTR_NO_INSTALLMENT', 0),  // 0 = taksit açık
    'max_installment' => env('PAYTR_MAX_INSTALLMENT', 12), // max 12 taksit
    'currency'        => env('PAYTR_CURRENCY', 'TL'),
    'lang'            => env('PAYTR_LANG', 'tr'),
    'iframe_url'      => 'https://www.paytr.com/odeme/api/get-token',
];