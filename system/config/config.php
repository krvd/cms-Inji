<?php

return [
    'autoloadModules' => [],
    'moduleRouter' => [
        'static' => 'StaticLoader',
        'msg' => 'SystemMessages',
    ],
    'assets' => [
        'js' => [
            [
                'file' => '/static/moduleAsset/Server/js/Server.js',
                'name' => 'Server'
            ]
        ]
    ]
];
