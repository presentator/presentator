<?php

return [
    'fuzzyUsersSearch' => false,
    'showCredits'      => true,
    'maxUploadSize'    => 15,

    // short/url lang code => full lang code
    'languages' => [
        'en' => 'en-US',
        'bg' => 'bg-BG',
        'pl' => 'pl-PL',
    ],

    'publicUrl'        => '',
    'activationSalt'   => '',
    'apiUserSecretKey' => '',

    'noreplyEmail'             => 'no-reply@example.com',
    'supportEmail'             => 'support@example.com',
    'passwordResetTokenExpire' => 3600,
    'rememberMeDuration'       => 3600 * 24 * 30,

    'facebookUrl'  => 'https://www.facebook.com/presentator.io',
    'githubUrl'    => 'https://github.com/ganigeorgiev/presentator',
    'issuesUrl'    => 'https://github.com/ganigeorgiev/presentator/issues',
    'supportUsUrl' => 'https://presentator.io/en/support-us',

    'facebookAuth' => [
        'clientId'     => '',
        'clientSecret' => '',
    ],

    'recaptcha' => [
        'siteKey'   => '',
        'secretKey' => '',
    ],
];
