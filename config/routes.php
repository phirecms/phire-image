<?php

return [
    APP_URI => [
        '/image[/:id]' => [
            'controller' => 'Phire\Image\Controller\IndexController',
            'action'     => 'index',
            'acl'        => [
                'resource'   => 'image-editor',
                'permission' => 'index'
            ]
        ],
        '/image/json' => [
            'controller' => 'Phire\Image\Controller\IndexController',
            'action'     => 'json',
            'acl'        => [
                'resource'   => 'image-editor',
                'permission' => 'json'
            ]
        ]
    ]
];
