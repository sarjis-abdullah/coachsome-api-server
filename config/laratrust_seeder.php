<?php

use App\Data\Constants;

return [
    'role_structure' => [
        Constants::ROLE_KEY_SUPER_ADMIN => [
            'user' => 'c,r,u,d',
            'acl' => 'c,r,u,d',
            'profile' => 'r,u'
        ],
        Constants::ROLE_KEY_ADMIN => [
            'users' => 'c,r,u,d',
            'profile' => 'r,u'
        ],
        Constants::ROLE_KEY_STAFF => [
            'user' => 'c,r,u,d',
            'profile' => 'r,u'
        ],
        Constants::ROLE_KEY_COACH => [
            'profile' => 'r,u'
        ],
        Constants::ROLE_KEY_ATHLETE => [
            'profile' => 'r,u'
        ],
    ],
    'permissions_map' => [
        'c' => 'create',
        'r' => 'read',
        'u' => 'update',
        'd' => 'delete'
    ]
];
