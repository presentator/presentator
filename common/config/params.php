<?php

return [
    // base url of the app service used for building the absolute url of the uploaded screens
    // (required for backward compatability with the old api service)
    'publicUrl' => '',

    // maximum allowed upload size (in MB)
    'maxUploadSize' => 15,

    // list of email address domains that are allowed to register (eg. `['example.com', 'test.com']`)
    // leave empty to disable the restriction
    'allowedRegistrationDomains' => [],

    // flag used to enable/disable project admins search by only a part of their email address or name
    'fuzzyUsersSearch' => false,

    // flag used to show/hide info about the project author (usually placed in the footer)
    'showCredits' => true,

    // whether to store the mails that need to be send in MailQueue table
    // and use a cron job to process them OR send them directly during runtime
    // @see `common\components\swiftmailer\CMessage`
    'useMailQueue' => false,

    // whether to purge processed MailQueue records on success
    // @see `\console\controllers\MailsController::actionProcess()`
    'purgeSentMails' => true,

    // email address that is used for sending system emails
    'noreplyEmail' => 'no-reply@example.com',

    // email address that is intented to process client's emails
    'supportEmail' => 'support@example.com',

    // API user auth token duration time in seconds
    'apiUserTokenExpire' => 86400,

    // password reset token duration time in seconds
    'passwordResetTokenExpire' => 3600,

    // email change token duration time in seconds
    'emailChangeTokenExpire' => 1800,

    // user session duration time in seconds
    'rememberMeDuration' => 3600 * 24 * 30,

    // url to the platform Terms and Conditions page
    'termsUrl' => 'https://presentator.io/terms-and-conditions',

    // url to the Support page of the platform, usually located in the footer (leave empty to hide)
    'supportUrl' => 'https://presentator.io/support-us',

    // url to the Facebook page of the platform, usually located in the footer (leave empty to hide)
    'facebookUrl'  => 'https://www.facebook.com/presentator.io',

    // url to the GitHub page of the platform, usually located in the footer (leave empty to hide)
    'githubUrl' => 'https://github.com/ganigeorgiev/presentator',

    // url to the GitHub Issues page of the platform, usually located in the footer (leave empty to hide)
    'issuesUrl' => 'https://github.com/ganigeorgiev/presentator/issues',

    // FB auth client settings (to enable both properties must be set!)
    'facebookAuth' => [
        'clientId'     => '',
        'clientSecret' => '',
    ],

    // Google+ auth client settings (to enable both properties must be set!)
    'googleAuth' => [
        'clientId'     => '',
        'clientSecret' => '',
    ],

    // ReCaptcha to prevent login brute force attacks (to enable both properties must be set!)
    'recaptcha' => [
        'siteKey' => '',
        'secretKey' => '_dont_leave_empty_',
    ],

    // secret key part of the user auth mechanism
    // (should be auto populated in params-local.php during the init process)
    'activationSalt'   => '',

    // secret key part of the api user auth mechanism
    // (should be auto populated in params-local.php during the init process)
    'apiUserSecretKey' => '',

    // lists with supported languages
    // short/url lang code => full lang code
    'languages' => [
        'en'    => 'en-US',
        'bg'    => 'bg-BG',
        'pl'    => 'pl-PL',
        'fr'    => 'fr-FR',
        'pt-br' => 'pt-BR',
        'de'    => 'de-DE',
        'es'    => 'es-ES',
        'sq-al' => 'sq-AL',
    ],

    // the current application running version used for update checks (will be auto changed on update)
    'currentVersion' => '1.9.1',

    // url to a service that checks whether the provided version is the latest one
    'versionCheckUrl' => 'https://presentator.io/downloads/check',

    // link to the latest archive (build) version
    'latestVersionArchiveUrl' => 'https://presentator.io/downloads/latest',
];
