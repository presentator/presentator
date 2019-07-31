<?php
return [
    // Screen 1001
    [
        'id'        => 1001,
        'replyTo'   => null,
        'screenId'  => 1001,
        'from'      => 'test@example.com',
        'message'   => 'Lorem ipsum',
        'left'      => 10.50,
        'top'       => 50,
        'status'    => 'pending',
        'createdAt' => '2019-06-30 01:00:00',
        'updatedAt' => '2019-06-30 01:00:00',
    ],
    [
        'id'        => 1002,
        'replyTo'   => 1001,
        'screenId'  => 1001,
        'from'      => 'test3@example.com',
        'message'   => 'Reply test...',
        'left'      => 10.50,
        'top'       => 50,
        'status'    => 'pending',
        'createdAt' => '2019-06-30 02:00:00',
        'updatedAt' => '2019-06-30 02:00:00',
    ],

    // Screen 1002
    [
        'id'        => 1003,
        'replyTo'   => null,
        'screenId'  => 1002,
        'from'      => 'test@example.com',
        'message'   => 'Lorem ipsum',
        'left'      => 0,
        'top'       => 0,
        'status'    => 'resolved',
        'createdAt' => '2019-06-30 03:00:00',
        'updatedAt' => '2019-06-30 03:00:00',
    ],

    // Screen 1003 - no comments

    // Screen 1004 - no comments

    // Screen 1005
    [
        'id'        => 1004,
        'replyTo'   => null,
        'screenId'  => 1005,
        'from'      => 'test2@example.com',
        'message'   => 'Lorem ipsum',
        'left'      => 10,
        'top'       => 10,
        'status'    => 'pending',
        'createdAt' => '2019-06-30 04:00:00',
        'updatedAt' => '2019-06-30 04:00:00',
    ],
    [
        'id'        => 1005,
        'replyTo'   => null,
        'screenId'  => 1005,
        'from'      => 'test3@example.com',
        'message'   => 'Lorem ipsum',
        'left'      => 30,
        'top'       => 50,
        'status'    => 'pending',
        'createdAt' => '2019-06-30 05:00:00',
        'updatedAt' => '2019-06-30 05:00:00',
    ],

    // Screen 1006 - no comments

    // Screen 1007
    [
        'id'        => 1006,
        'replyTo'   => null,
        'screenId'  => 1007,
        'from'      => 'guest@example.com',
        'message'   => 'Lorem ipsum',
        'left'      => 30,
        'top'       => 50,
        'status'    => 'pending',
        'createdAt' => '2019-06-30 06:00:00',
        'updatedAt' => '2019-06-30 06:00:00',
    ],

    // Screen 1008 - no comments
];
