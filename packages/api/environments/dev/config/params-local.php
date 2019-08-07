<?php
// List with all available application parameters could be found at https://github.com/presentator/presentator-api/tree/master/config/params.php
return [
    'baseStorageUrl'            => 'http://localhost:8081/storage',
    'appUrl'                    => 'http://localhost:8080/#/',
    'authClientRedirectUri'     => 'http://localhost:8080/#/auth-callback',
    'activationUrl'             => 'http://localhost:8080/#/activate/{token}',
    'passwordResetUrl'          => 'http://localhost:8080/#/reset-password/{token}',
    'emailChangeUrl'            => 'http://localhost:8080/#/change-email/{token}',
    'projectLinkUrl'            => 'http://localhost:8080/#/{slug}',
    'projectLinkCommentViewUrl' => 'http://localhost:8080/#/{slug}/prototypes/{prototypeId}/screens/{screenId}?mode=comments&commentId={commentId}',
    'commentViewUrl'            => 'http://localhost:8080/#/projects/{projectId}/prototypes/{prototypeId}/screens/{screenId}?mode=comments&commentId={commentId}',

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
