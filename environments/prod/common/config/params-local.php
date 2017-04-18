<?php
return [
    // app domain/vhost
    'publicUrl' => 'http://app.presentator.dev',

    // !!! insert a secret key in the following (if it is empty) - this is required for User validation
    'activationSalt'   => '',

    // !!! insert a secret key in the following (if it is empty) - this is required for API User authentication
    'apiUserSecretKey' => '',

    // facebook login app data
    'facebookAuth' => [
        'clientId'     => '',
        'clientSecret' => '',
    ],

    // recaptcha keys (prevent login brute force attacks)
    'recaptcha' => [
        'siteKey'   => '',
        'secretKey' => '',
    ],

    // service email address
    'noreplyEmail' => 'no-reply@example.com',
    'supportEmail' => 'support@example.com',
];
