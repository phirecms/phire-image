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
        'adapter'       => 'gd',
        'history'       => 10,
        'install' => function() {
            mkdir($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/image-history');
            chmod($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/image-history', 0777);
            copy(
                $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/index.html',
                $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/image-history/index.html'
            );
            chmod($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/image-history/index.html', 0777);
        },
        'uninstall' => function() {
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/image-history')) {
                $dir = new \Pop\File\Dir($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/image-history');
                $dir->emptyDir(true);
            }
        }
    ]
];
