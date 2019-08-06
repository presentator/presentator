<?php
/**
 * The manifest of files that are local to specific environment.
 * This file returns a list of environments that the application
 * may be installed under. The returned data must be in the following
 * format:
 *
 * ```php
 * return [
 *     'environment name' => [
 *         'path' => 'directory storing the local files',
 *         'skipFiles'  => [
 *             // list of files that should only copied once and skipped if they already exist
 *         ],
 *         'setWritable' => [
 *             // list of directories that should be set writable
 *         ],
 *         'setExecutable' => [
 *             // list of files that should be set executable
 *         ],
 *         'setRandomKey' => [
 *             // list of `[paramName, filePath, keyLenght]` items that
 *             // generates and inserts random string key for `paramName`
 *             // with length `keyLength` (optional, default to 32)
 *             // in file located at `filePath`
 *         ],
 *         'createSymlink' => [
 *             // list of symlinks to be created. Keys are symlinks, and values are the targets.
 *         ],
 *     ],
 * ];
 * ```
 */

$base = [
    'setWritable' => [
        'runtime',
        'web/assets',
        'web/storage',
    ],
    'setRandomKey' => [
        ['cookieValidationKey',    'config/api-local.php'],
        ['storageKeysSalt',        'config/params-local.php'],
        ['accessTokenSecret',      'config/params-local.php'],
        ['activationTokenSecret',  'config/params-local.php'],
        ['emailChangeTokenSecret', 'config/params-local.php'],
        ['previewTokenSecret',     'config/params-local.php'],
    ],
];

return [
    'Development' => array_merge($base, [
        'path' => 'dev',
        'setExecutable' => [
            'yii',
            'yii_test',
        ],
    ]),
    'Production' => array_merge($base, [
        'path' => 'prod',
        'setExecutable' => [
            'yii',
        ],
    ]),
    'Starter' => array_merge($base, [
        'path' => 'starter',
        'setWritable' => [
            'runtime',
            'web/api/assets',
            'web/storage',
        ],
        'setExecutable' => [
            'yii',
        ],
    ]),
];
