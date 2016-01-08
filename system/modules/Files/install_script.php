<?php

return function($step = NULL, $params = []) {
    $types = [
        [
            'dir' => '/static/mediafiles/images/',
            'ext' => 'png',
            'group' => 'image',
            'allow_resize' => 1,
        ],
        [
            'dir' => '/static/mediafiles/images/',
            'ext' => 'jpeg',
            'group' => 'image',
            'allow_resize' => 1,
        ],
        [
            'dir' => '/static/mediafiles/images/',
            'ext' => 'jpg',
            'group' => 'image',
            'allow_resize' => 1,
        ],
        [
            'dir' => '/static/mediafiles/images/',
            'ext' => 'gif',
            'group' => 'image',
            'allow_resize' => 1,
        ],
    ];
    foreach ($types as $type) {
        $typeObject = new \Files\Type($type);
        $typeObject->save();
    }
};
