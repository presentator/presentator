<?php

return [
    'fuzzyUsersSearch' => false,
    'showCredits'      => true,
    'maxUploadSize'    => 15,

    // whether to store the mails that need to be send in MailQueue table
    // and use a cron job to process them or send them directly on runtime
    // @see `common\components\swiftmailer\CMessage`
    'useMailQueue' => false,

    // whether to purge processed MailQueue records on success
    // @see `\console\controllers\MailsController::actionProcess()`
    'purgeSentMails' => true,

    // short/url lang code => full lang code
    'languages' => [
        'en'    => 'en-US',
        'bg'    => 'bg-BG',
        'pl'    => 'pl-PL',
        'fr'    => 'fr-FR',
        'pt-br' => 'pt-BR',
        'de'    => 'de-DE',
        'es'    => 'es-ES',
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
