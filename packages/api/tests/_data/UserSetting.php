<?php
use presentator\api\models\UserSetting;

return [
    // user 1001
    [
        'id'        => 1001,
        'userId'    => 1001,
        'type'      => UserSetting::TYPE['BOOLEAN'],
        'name'      => UserSetting::NOTIFY_ON_EACH_COMMENT,
        'value'     => 'true',
        'createdAt' => '2019-06-28 01:00:00',
        'updatedAt' => '2019-06-28 01:00:00',
    ],
    [
        'id'        => 1002,
        'userId'    => 1001,
        'type'      => UserSetting::TYPE['BOOLEAN'],
        'name'      => UserSetting::NOTIFY_ON_MENTION,
        'value'     => 'false',
        'createdAt' => '2019-06-28 01:00:00',
        'updatedAt' => '2019-06-28 01:00:00',
    ],

    // user 1002
    [
        'id'        => 1003,
        'userId'    => 1002,
        'type'      => UserSetting::TYPE['BOOLEAN'],
        'name'      => UserSetting::NOTIFY_ON_EACH_COMMENT,
        'value'     => 'true',
        'createdAt' => '2019-06-28 02:00:00',
        'updatedAt' => '2019-06-28 02:00:00',
    ],
    [
        'id'        => 1004,
        'userId'    => 1002,
        'type'      => UserSetting::TYPE['BOOLEAN'],
        'name'      => UserSetting::NOTIFY_ON_MENTION,
        'value'     => 'true',
        'createdAt' => '2019-06-28 02:00:00',
        'updatedAt' => '2019-06-28 02:00:00',
    ],

    // user 1003
    [
        'id'        => 1005,
        'userId'    => 1003,
        'type'      => UserSetting::TYPE['BOOLEAN'],
        'name'      => UserSetting::NOTIFY_ON_EACH_COMMENT,
        'value'     => 'false',
        'createdAt' => '2019-06-28 03:00:00',
        'updatedAt' => '2019-06-28 03:00:00',
    ],
    [
        'id'        => 1006,
        'userId'    => 1003,
        'type'      => UserSetting::TYPE['BOOLEAN'],
        'name'      => UserSetting::NOTIFY_ON_MENTION,
        'value'     => 'true',
        'createdAt' => '2019-06-28 03:00:00',
        'updatedAt' => '2019-06-28 03:00:00',
    ],

    // user 1004
    [
        'id'        => 1007,
        'userId'    => 1004,
        'type'      => UserSetting::TYPE['BOOLEAN'],
        'name'      => UserSetting::NOTIFY_ON_EACH_COMMENT,
        'value'     => 'false',
        'createdAt' => '2019-06-28 04:00:00',
        'updatedAt' => '2019-06-28 04:00:00',
    ],
    [
        'id'        => 1008,
        'userId'    => 1004,
        'type'      => UserSetting::TYPE['BOOLEAN'],
        'name'      => UserSetting::NOTIFY_ON_MENTION,
        'value'     => 'false',
        'createdAt' => '2019-06-28 04:00:00',
        'updatedAt' => '2019-06-28 04:00:00',
    ],
];
