<?php

return [
    'autoloadModules' => [],
    'modules' => [
        'StaticLoader',
        'Ui',
        'Db',
        'View',
        'Libs',
        'Server',
        'Modules',
        'Apps',
    ],
    'moduleRouter' => [
        'static' => 'StaticLoader'
    ],
    'assets' => [
        'js' => [
            [
                'file' => '/static/moduleAsset/Server/js/Server.js',
                'name' => 'Server',
                'libs' => ['noty']
            ]
        ]
    ]
];
