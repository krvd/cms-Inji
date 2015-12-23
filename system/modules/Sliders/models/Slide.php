<?php

/**
 * Slide
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Sliders;

class Slide extends \Model
{
    public static $objectName = "Слайд";
    public static $cols = [
        'name' => ['type' => 'text'],
        'link' => ['type' => 'text'],
        'description' => ['type' => 'html'],
        'image_file_id' => ['type' => 'image'],
        'sort' => ['type' => 'text'],
        'slider_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'slider'],
        'user_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'user'],
        'weight' => ['type' => 'number'],
        'date_create' => ['type' => 'dateTime'],
    ];
    public static $labels = [
        'name' => 'Имя',
        'link' => 'Ссылка',
        'description' => 'Описание',
        'date_create' => 'Дата создания',
        'sort' => 'Сортировка',
        'slider_id' => 'Слайдер',
        'user_id' => 'Создатель',
        'weight' => 'Вес',
        'image_file_id' => 'Изображение',
    ];
    static $dataManagers = [
        'manager' => [
            'name' => 'Слайды',
            'cols' => [
                'name', 'link', 'date_create'
            ],
            'sortMode' => true
        ],
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name', 'link'],
                ['image_file_id'],
                ['description'],
            ],
        ],
    ];

    public static function relations()
    {
        return [
            'slider' => [
                'model' => 'Sliders\Slider',
                'col' => 'slider_id'
            ],
            'image' => [
                'model' => 'Files\File',
                'col' => 'image_file_id'
            ],
            'user' => [
                'model' => 'Users\User',
                'col' => 'user_id'
            ]
        ];
    }

}
