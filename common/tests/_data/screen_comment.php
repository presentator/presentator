<?php
use \common\models\ScreenComment;

return [
    // screen 1001
    [
        'id'        => 1001,
        'replyTo'   => null,
        'screenId'  => 1001,
        'from'      => 'some_test_email@presentator.io',
        'message'   => 'Lorem ipsum dolor sit amet...',
        'posX'      => 200,
        'posY'      => 100,
        'createdAt' => 1488526394,
        'updatedAt' => 1488526394,
    ],
    [
        'id'        => 1002,
        'replyTo'   => null,
        'screenId'  => 1001,
        'from'      => 'some_test_email@presentator.io',
        'message'   => 'Lorem ipsum dolor sit amet...',
        'posX'      => 220,
        'posY'      => 150,
        'createdAt' => 1488526394,
        'updatedAt' => 1488526394,
    ],
    [
        'id'        => 1003,
        'replyTo'   => 1002,
        'screenId'  => 1001,
        'from'      => 'test2@presentator.io',
        'message'   => 'Reply message...',
        'posX'      => 220,
        'posY'      => 150,
        'createdAt' => 1488526394,
        'updatedAt' => 1488526394,
    ],
    // screen 1003
    [
        'id'        => 1004,
        'replyTo'   => null,
        'screenId'  => 1003,
        'from'      => 'test3@presentator.io',
        'message'   => 'Comment from the author...',
        'posX'      => 12,
        'posY'      => 59,
        'createdAt' => 1488526394,
        'updatedAt' => 1488526394,
    ],
    // screen 1004
    [
        'id'        => 1005,
        'replyTo'   => null,
        'screenId'  => 1004,
        'from'      => 'test3@presentator.io',
        'message'   => 'Comment target...',
        'posX'      => 100,
        'posY'      => 50,
        'createdAt' => 1488526394,
        'updatedAt' => 1488526394,
    ],
    [
        'id'        => 1006,
        'replyTo'   => 1005,
        'screenId'  => 1004,
        'from'      => 'loremipsum@presentator.io',
        'message'   => 'Comment reply...',
        'posX'      => 100,
        'posY'      => 50,
        'createdAt' => 1488526394,
        'updatedAt' => 1488526394,
    ],
];
