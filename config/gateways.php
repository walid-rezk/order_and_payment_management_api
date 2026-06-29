<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payment Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Configure credentials for each payment gateway. These values are
    | read from your .env file.
    |
    */

    'credit_card' => [
        'api_key' => env('CREDIT_CARD_API_KEY', ''),
        'secret'  => env('CREDIT_CARD_SECRET', ''),
    ],

    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID', ''),
        'secret'    => env('PAYPAL_SECRET', ''),
    ],

];
