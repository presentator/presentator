<?php
return [
    // base public url to the storage directory (could be also a cdn address if you use S3 or other storage mechanism)
    'baseStorageUrl' => 'https://example.com/storage',

    // url to the SPA (used mainly in mail templates)
    'appUrl' => 'https://example.com/#/',

    // indicates the URI to return the user after oauth authorization
    'authClientRedirectUri' => 'https://example.com/#/auth-callback/',

    // url to the user activation confirmation page (used mainly in mail templates)
    // the following variables are available: {token}
    'activationUrl' => 'https://example.com/#/activate/{token}',

    // url to the user password reset confirmation page (used mainly in mail templates)
    // the following variables are available: {token}
    'passwordResetUrl' => 'https://example.com/#/reset-password/{token}',

    // url to the user email change confirmation page (used mainly in mail templates)
    // the following variables are available: {token}
    'emailChangeUrl' => 'https://example.com/#/change-email/{token}',

    // url to the project preview/link page (used mainly in mail templates)
    // the following variables are available: {slug}
    'projectLinkUrl' => 'https://example.com/#/{slug}',

    // url to the guest comment preview page (used mainly in mail templates)
    // the following variables are available: {slug}, {prototypeId}, {screenId}, {commentId}
    'projectLinkCommentViewUrl' => 'https://example.com/#/{slug}/prototypes/{prototypeId}/screens/{screenId}?mode=comments&commentId={commentId}',

    // url to the admin comment view page (used mainly in mail templates)
    // the following variables are available: {projectId}, {prototypeId}, {screenId}, {commentId}
    'commentViewUrl' => 'https://example.com/#/projects/{projectId}/prototypes/{prototypeId}/screens/{screenId}?mode=comments&commentId={commentId}',

    // email address used to send system emails from
    'noreplyEmail' => 'noreply@example.com',

    // support email address (also used for receiving users feedback)
    'supportEmail' => 'support@example.com',

    // list of email address domains that are allowed to register (eg. `['example.com', 'test.com']`)
    // or in other words - only emails from domains that are listed here could register
    // (leave empty to skip the filter)
    'onlyDomainsRegisterFilter' => [],

    // list of email address domains that are not allowed to register (eg. `['example.com', 'test.com']`)
    // or in other words - only emails from domains that are NOT listed here could register
    // (leave empty to skip the filter)
    'exceptDomainsRegisterFilter' => [],

    // flag used to enable/disable project admins search by only part of their email address or name
    'looseProjectUsersSearch' => false,

    // optional salt for the storage directories to prevent files enumeration
    // NB! Changing the value after initialization could result in corrupted or invalid files path.
    // (should be auto populated in `params-local.php` on application init)
    'storageKeysSalt' => '',

    // user password reset token duration time in seconds (default to 1 hour)
    'passwordResetTokenDuration' => 3600,

    // user access token duration time in seconds (default to 2 weeks)
    'accessTokenDuration' => 1209600,

    // user access token secret key
    // (should be auto populated in `params-local.php` on application init)
    'accessTokenSecret' => '',

    // user activation token duration time in seconds (default to 2 weeks)
    'activationTokenDuration' => 1209600,

    // user activation token secret key
    // (should be auto populated in `params-local.php` on application init)
    'activationTokenSecret' => '',

    // user email change token duration time in seconds (default to 1 hour)
    'emailChangeTokenDuration' => 3600,

    // user email change token secret key
    // (should be auto populated in `params-local.php` on application init)
    'emailChangeTokenSecret' => '',

    // project preview token duration time in seconds (default to 30 days)
    'previewTokenDuration' => 2592000,

    // project preview token secret key
    // (should be auto populated in `params-local.php` on application init)
    'previewTokenSecret' => '',

    // maximum allowed user avatar file upload size in MB
    'maxAvatarUploadSize' => 3,

    // list of allowed user avatar file formats to upload
    'allowedAvatarMimetypes' => [
        'image/png', 'image/jpeg', 'image/svg+xml', 'image/bmp',
    ],

    // maximum allowed scren file upload size in MB
    'maxScreenUploadSize' => 10,

    // list of allowed screen file formats to upload
    'allowedScreenMimeTypes' => [
        'image/png', 'image/jpeg', 'image/svg+xml', 'image/bmp',
    ],

    // maximum allowed scren file upload size in MB
    'maxGuidelineAssetUploadSize' => 15,

    // list of allowed guideline asset file formats to upload
    'allowedGuidelineAssetMimeTypes' => [
        // png
        'image/png',
        // jpg/jpeg
        'image/jpeg',
        // svg
        'image/svg+xml',
        // bmp
        'image/bmp',
        // eot
        'application/vnd.ms-fontobject',
        // ttf
        'font/ttf', 'application/x-font-ttf',
        // woff/woff2
        'font/woff', 'font/woff2', 'application/font-woff',
        // psd
        'image/vnd.adobe.photoshop',
        // ai, eps
        'application/postscript',
        // pdf
        'application/pdf',
        // zip
        'application/zip',
        // rar
        'application/x-rar-compressed',
    ],
];
