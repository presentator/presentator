<?php
use \common\models\Version;

return [
    // project 1001
    [
        'id'          => 1001,
        'projectId'   => 1001,
        'title'       => 'Test version title',
        'type'        => Version::TYPE_DESKTOP,
        'subtype'     => null,
        'scaleFactor' => 2,
        'order'       => 1,
        'createdAt'   => 1488526394,
        'updatedAt'   => 1488526394,
    ],
    [
        'id'          => 1002,
        'projectId'   => 1001,
        'title'       => '',
        'type'        => Version::TYPE_DESKTOP,
        'subtype'     => null,
        'scaleFactor' => 2,
        'order'       => 2,
        'createdAt'   => 1488526394,
        'updatedAt'   => 1488526394,
    ],
    // project 1002
    [
        'id'          => 1003,
        'projectId'   => 1002,
        'title'       => null,
        'type'        => Version::TYPE_TABLET,
        'subtype'     => 21,
        'scaleFactor' => 0.5,
        'order'       => 1,
        'createdAt'   => 1488526394,
        'updatedAt'   => 1488526394,
    ],
    // project 1003
    [
        'id'          => 1004,
        'projectId'   => 1003,
        'title'       => null,
        'type'        => Version::TYPE_MOBILE,
        'subtype'     => 31,
        'scaleFactor' => 0,
        'order'       => 1,
        'createdAt'   => 1488526394,
        'updatedAt'   => 1488526394,
    ],
    [
        'id'          => 1005,
        'projectId'   => 1003,
        'title'       => null,
        'type'        => Version::TYPE_MOBILE,
        'subtype'     => 31,
        'scaleFactor' => 0,
        'order'       => 2,
        'createdAt'   => 1488526394,
        'updatedAt'   => 1488526394,
    ],
    // project 1004
    [
        'id'          => 1006,
        'projectId'   => 1004,
        'title'       => null,
        'type'        => Version::TYPE_DESKTOP,
        'subtype'     => null,
        'scaleFactor' => 1,
        'order'       => 1,
        'createdAt'   => 1488526394,
        'updatedAt'   => 1488526394,
    ],
];
