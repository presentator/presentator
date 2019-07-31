<?php
// list with all available application parameters could be found at https://github.com/presentator/presentator-api/tree/master/config/params.php
return [
    'baseStorageUrl'            => 'https://example.com/storage',
    'appUrl'                    => 'https://example.com/#/',
    'authClientRedirectUri'     => 'https://example.com/#/auth-callback/',
    'activationUrl'             => 'https://example.com/#/activate/{token}',
    'passwordResetUrl'          => 'https://example.com/#/reset-password/{token}',
    'emailChangeUrl'            => 'https://example.com/#/change-email/{token}',
    'projectLinkUrl'            => 'https://example.com/#/{slug}',
    'projectLinkCommentViewUrl' => 'https://example.com/#/{slug}/prototypes/{prototypeId}/screens/{screenId}?mode=comments&commentId={commentId}',
    'commentViewUrl'            => 'https://example.com/#/projects/{projectId}/prototypes/{prototypeId}/screens/{screenId}?mode=comments&commentId={commentId}',
    'noreplyEmail'              => 'noreply@example.com',
    'supportEmail'              => 'support@example.com',

    // !!! insert secret unique keys in the following (if empty)
    'storageKeysSalt'        => '',
    'accessTokenSecret'      => '',
    'activationTokenSecret'  => '',
    'emailChangeTokenSecret' => '',
    'previewTokenSecret'     => '',
];
