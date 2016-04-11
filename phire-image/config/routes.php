<?php

return [
    APP_URI => [
        '/image[/]' => [
            'controller' => 'Phire\Image\Controller\IndexController',
            'action'     => 'index',
            'acl'        => [
                'resource'   => 'image-editor',
                'permission' => 'index'
            ]
        ]
    ]
];
