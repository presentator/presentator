<?php
// List with all available application parameters could be found at https://github.com/presentator/presentator-api/tree/master/config/params.php
return [
    'baseStorageUrl'            => 'https://example.com/storage',
    'appUrl'                    => 'https://example.com/#/',
    'authClientRedirectUri'     => 'https://example.com/#/auth-callback',
    'activationUrl'             => 'https://example.com/#/activate/{token}',
    'passwordResetUrl'          => 'https://example.com/#/reset-password/{token}',
    'emailChangeUrl'            => 'https://example.com/#/change-email/{token}',
    'projectLinkUrl'            => 'https://example.com/#/{slug}',
    'projectLinkCommentViewUrl' => 'https://example.com/#/{slug}/prototypes/{prototypeId}/screens/{screenId}?mode=comments&commentId={commentId}',
    'commentViewUrl'            => 'https://example.com/#/projects/{projectId}/prototypes/{prototypeId}/screens/{screenId}?mode=comments&commentId={commentId}',
    'noreplyEmail'              => 'noreply@example.com',
    'supportEmail'              => 'support@example.com',

    // !!!
    // The application init script will try to populate the following keys for you.
    // If for some reason their value is empty after app initialization,
    // you must set a unique value for each manually (eg. '652PfPqYpXY2o0FJC18YgBQn6zIeTqDP').
    //
    // Also be cautious when changing `storageKeysSalt` after the initial
    // installation setup because it could results in corrupted storage files path.
    // !!!
    'storageKeysSalt'        => '',
    'accessTokenSecret'      => '',
    'activationTokenSecret'  => '',
    'emailChangeTokenSecret' => '',
    'previewTokenSecret'     => '',
];
