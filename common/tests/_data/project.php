<?php
use \common\models\Project;

return [
    [
        'id'           => 1001,
        'title'        => 'Lorem ipsum title',
        'type'         => Project::TYPE_DESKTOP,
        'subtype'      => null,
        'passwordHash' => null,
        'createdAt'    => 1488526394,
        'updatedAt'    => 1488526394,
    ],
    [
        'id'           => 1002,
        'title'        => 'Lorem ipsum title',
        'type'         => Project::TYPE_TABLET,
        'subtype'      => 21,
        'passwordHash' => '$2a$06$ZHU1WxTPs7MBz/AN1sVEBOz4yo7XjI4HDrmJopYW/11vgmBphT.I6', // 123456
        'createdAt'    => 1488526380,
        'updatedAt'    => 1488526380,
    ],
    [
        'id'           => 1003,
        'title'        => 'Lorem ipsum title',
        'type'         => Project::TYPE_MOBILE,
        'subtype'      => 31,
        'passwordHash' => '$2a$06$BuN9F.Uar6kajZkLILRp0eSZ2ksnEr01VQPZFNXC7qqP9F8yPkg5.', // 123456
        'createdAt'    => 1488526390,
        'updatedAt'    => 1488526390,
    ],
    [
        'id'           => 1004,
        'title'        => 'Project test title',
        'type'         => Project::TYPE_DESKTOP,
        'subtype'      => null,
        'passwordHash' => null,
        'createdAt'    => 1488526395,
        'updatedAt'    => 1488526395,
    ],
];
