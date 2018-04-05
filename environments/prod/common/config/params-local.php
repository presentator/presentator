<?php
// NB! Check `common/config/params.php` for list with all parameters.

$publicUrl = getenv('PUPLIC_URL') or 'http://app.presentator.local';

return [
    // base url of the app service used for building the absolute url of the uploaded screens
    // (required for backward compatability with the old api service)
    'publicUrl' => $publicUrl,

    // !!! insert a secret key in the following (if it is empty) - this is required for User validation
    'activationSalt' => '',

    // !!! insert a secret key in the following (if it is empty) - this is required for API User authentication
    'apiUserSecretKey' => '',

    // service email addresses
    'noreplyEmail' => 'no-reply@example.com',
    'supportEmail' => 'support@example.com',
];
