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

    // ReCaptcha to prevent login brute force attacks (to enable both properties must be set)
    'recaptcha' => [
        'siteKey'   => '',
        'secretKey' => '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe',
    ],

    // service email address
    'noreplyEmail' => 'no-reply@example.com',
    'supportEmail' => 'support@example.com',
];
