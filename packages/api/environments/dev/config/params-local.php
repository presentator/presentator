<?php
// list with all available application parameters could be found at https://github.com/presentator/presentator-api/tree/master/config/params.php
return [
    'baseStorageUrl'            => 'http://localhost:8081/storage',
    'appUrl'                    => 'http://localhost:8080/#/',
    'authClientRedirectUri'     => 'http://localhost:8080/#/auth-callback/',
    'activationUrl'             => 'http://localhost:8080/#/activate/{token}',
    'passwordResetUrl'          => 'http://localhost:8080/#/reset-password/{token}',
    'emailChangeUrl'            => 'http://localhost:8080/#/change-email/{token}',
    'projectLinkUrl'            => 'http://localhost:8080/#/{slug}',
    'projectLinkCommentViewUrl' => 'http://localhost:8080/#/{slug}/prototypes/{prototypeId}/screens/{screenId}?mode=comments&commentId={commentId}',
    'commentViewUrl'            => 'http://localhost:8080/#/projects/{projectId}/prototypes/{prototypeId}/screens/{screenId}?mode=comments&commentId={commentId}',

    // !!! insert secret unique keys in the following (if empty)
    'storageKeysSalt'        => '',
    'accessTokenSecret'      => '',
    'activationTokenSecret'  => '',
    'emailChangeTokenSecret' => '',
    'previewTokenSecret'     => '',
];
