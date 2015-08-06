<?php

/**
 * Item name
 *
 * Info
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Migrations;

class Walker {

    public $migration = null;
    public $map = null;
    public $reader = null;
    public $mapPath = null;
    public $realPath = null;

    function walk($path = '/') {
        if (!$this->realPath) {
            $this->realPath = $path;
        }
        foreach ($this->reader->readPath() as $key => $item) {
            $type = $this->getInfo($key, $this->realPath);
            if ($type) {
                if ($type->type == 'container') {
                    $walker = clone $this;
                    $walker->reader = $item;
                    $walker->realPath = $this->realPath . $key . '/';
                    $walker->walk();
                } elseif ($type->type == 'object') {
                    $objectParser = new Parser\Object();
                    $objectParser->object = Migration\Object::get($type->object_id);
                    $objectParser->reader = $item;
                    $objectParser->parse();
                }
            }
            $object = Migration\Object::get([
                        ['code', $key],
            ]);
            if ($object) {
                $objectParser = new Parser\Object();
                $objectParser->object = $object;
                $objectParser->reader = $item;
                $objectParser->parse();
            }
        }
    }

    function getInfo($item, $path) {
        $mapPath = Migration\Map\Path::get([
                    ['path', $path],
                    ['item', $item],
                    ['map_id', $this->map->id]
        ]);
        if ($mapPath && $mapPath->type) {
            return $mapPath;
        }
        if (!$mapPath) {
            $mapPath = new Migration\Map\Path();
            $mapPath->parent_id = $this->mapPath ? $this->mapPath->id : 0;
            $mapPath->path = $path;
            $mapPath->item = $item;
            $mapPath->map_id = $this->map->id;
            $mapPath->save();
        }
    }

}
