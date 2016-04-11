<?php
/**
 * Module Name: phire-image
 * Author: Nick Sagona
 * Description: This is the image editor module for Phire CMS 2
 * Version: 1.0
 */
return [
    'phire-image' => [
        'prefix'     => 'Phire\Image\\',
        'src'        => __DIR__ . '/../src',
        'routes'     => include 'routes.php',
        'resources'  => include 'resources.php',
        'nav.module' => [
            'name' => 'Image Editor',
            'href' => '/image',
            'acl' => [
                'resource'   => 'image-editor',
                'permission' => 'index'
            ]
        ],
        'editor_height' => 600,
        'adapter'       => 'gd'
    ]
];
