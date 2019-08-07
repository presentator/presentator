<?php
// List with all available application parameters could be found at https://github.com/presentator/presentator-api/tree/master/config/params.php
return [
    'baseStorageUrl'            => 'http://example.com/storage',
    'appUrl'                    => 'http://example.com/#/',
    'authClientRedirectUri'     => 'http://example.com/#/auth-callback',
    'activationUrl'             => 'http://example.com/#/activate/{token}',
    'passwordResetUrl'          => 'http://example.com/#/reset-password/{token}',
    'emailChangeUrl'            => 'http://example.com/#/change-email/{token}',
    'projectLinkUrl'            => 'http://example.com/#/{slug}',
    'projectLinkCommentViewUrl' => 'http://example.com/#/{slug}/prototypes/{prototypeId}/screens/{screenId}?mode=comments&commentId={commentId}',
    'commentViewUrl'            => 'http://example.com/#/projects/{projectId}/prototypes/{prototypeId}/screens/{screenId}?mode=comments&commentId={commentId}',
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
