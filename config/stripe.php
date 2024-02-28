<?php

return [

    'STRIPE_KEY' => env('STRIPE_KEY'),
    'STRIPE_SECRET' => env('STRIPE_SECRET'),
    'MIN_AMOUNT' => 10000,
    'SUCCESS_MSG' => 'Payment Successful!',
    'ERROR_MSG' => 'Payment Failed!',
    'STRIPE_KEY_ERROR_MSG' => 'Payment gateway authentication failed.',
];
