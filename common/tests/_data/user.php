<?php
use \common\models\User;

return [
    [
        'id'                 => 1001,
        'email'              => 'test1@presentator.io',
        'firstName'          => 'Gani',
        'lastName'           => 'Georgiev',
        'authKey'            => 'cut7WAOyLMe3aFh5cC6cszjZTtW7cpRN',
        'passwordHash'       => '$2a$06$TRlYHhAHRCfO11q8spPPu.GJlPBCrMltBr.u8U/h.jyEJJcOZzbWm', // 123456
        'passwordResetToken' => 'mmhZeoYehC0FFzgURpO625BQlLraoZVn_' . time(), // valid password reset token
        'emailChangeToken'   => md5('test_change@presentator.io') . '_' . time(), // valid email change token
        'status'             => User::STATUS_INACTIVE,
        'createdAt'          => 1488526394,
        'updatedAt'          => 1488526394,
    ],
    [
        'id'                 => 1002,
        'email'              => 'test2@presentator.io',
        'firstName'          => 'Ivan',
        'lastName'           => '',
        'authKey'            => '24vWU7dCVkpXEG4nDhj1aplzhf-1j3pJ',
        'passwordHash'       => '$2a$06$D.vvwAuHJKg37EXIQnY39ezf2U23YcW2KAgK91UCn9XMtg5f4q6Aa', // 123456
        'passwordResetToken' => null,
        'emailChangeToken'   => null,
        'status'             => User::STATUS_ACTIVE,
        'createdAt'          => 1488526394,
        'updatedAt'          => 1488526394,
    ],
    [
        'id'                 => 1003,
        'email'              => 'test3@presentator.io',
        'firstName'          => 'John',
        'lastName'           => '',
        'authKey'            => 'pevWUG47dCVkj1aplzhpXEnDhf-2G3pR',
        'passwordHash'       => '$2a$06$bVu5ROFjksz.0nj3AMQR0OZOvVniJaNsNkJCivYbqUvj7htjX60Ke', // 123456
        'passwordResetToken' => '62QehlLraoZVe5BoYnmzgURpOmhZC0FF_' . strtotime('-2 days'), // expired password reset token
        'emailChangeToken'   => md5('test_change@presentator.io') . '_' . strtotime('-2 days'), // expired email change token
        'status'             => User::STATUS_ACTIVE,
        'createdAt'          => 1488526394,
        'updatedAt'          => 1488526394,
    ],
    [
        'id'                 => 1004,
        'email'              => 'test4@presentator.io',
        'firstName'          => '',
        'lastName'           => 'Petrov',
        'authKey'            => 'tesWUGplzhf-47dCVkpXEnDhj1a2j3pL',
        'passwordHash'       => '$2a$06$588SkWzoJiBHvD0yQnwsFuZvZZSyKmJOoR3a2u5kueRu/jmHMjwje', // 123456
        'passwordResetToken' => 'QehlLraoZVmmhZC0FFzgURpO62e5BoYn_' . time(), // valid password reset token
        'emailChangeToken'   => md5('test5@presentator.io') . '_' . time(), // valid email change token (duplicate test)
        'status'             => User::STATUS_ACTIVE,
        'createdAt'          => 1488526394,
        'updatedAt'          => 1488526394,
    ],
    [
        'id'                 => 1005,
        'email'              => 'test5@presentator.io',
        'firstName'          => 'Lorem',
        'lastName'           => 'Ipsum',
        'authKey'            => 'VkpX-47dC1a2j3pLEnDhtesWUGplzhfj',
        'passwordHash'       => '$2a$06$588SkWzoJiBHvD0yQnwsFuZvZZSyKmJOoR3a2u5kueRu/jmHMjwje', // 123456
        'passwordResetToken' => null,
        'emailChangeToken'   => md5('test_change2@presentator.io') . '_' . time(), // valid email change token
        'status'             => User::STATUS_ACTIVE,
        'createdAt'          => 1488526394,
        'updatedAt'          => 1488526394,
    ],
    [
        'id'                 => 1006,
        'email'              => 'test6@presentator.io',
        'firstName'          => 'John',
        'lastName'           => 'Doe',
        'authKey'            => 'GplznDhteXh-sWU47dC1a2j3pLfVkpEj',
        'passwordHash'       => '$2a$06$588SkWzoJiBHvD0yQnwsFuZvZZSyKmJOoR3a2u5kueRu/jmHMjwje', // 123456
        'passwordResetToken' => null,
        'emailChangeToken'   => null,
        'status'             => User::STATUS_ACTIVE,
        'createdAt'          => 1488526394,
        'updatedAt'          => 1488526394,
    ],
    [
        'id'                 => 1007,
        'email'              => 'test7@presentator.io',
        'firstName'          => '',
        'lastName'           => '',
        'authKey'            => 'GpXhdWU47dC1a2j3pLfjlzVkpEnDhte',
        'passwordHash'       => '$2a$06$588SkWzoJiBHvD0yQnwsFuZvZZSyKmJOoR3a2u5kueRu/jmHMjwje', // 123456
        'passwordResetToken' => null,
        'emailChangeToken'   => null,
        'status'             => User::STATUS_ACTIVE,
        'createdAt'          => 1488526394,
        'updatedAt'          => 1488526394,
    ],
];
