<?php

/**
 * Data tree walker
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Migrations;

class Walker
{
    public $migration = null;
    public $map = null;
    public $reader = null;
    public $mapPath = null;
    public $mapPathParent = null;
    public $realPath = null;

    function walk($path = '/')
    {
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
                    $walker->mapPathParent = $this->mapPath;
                    $walker->walk();
                } elseif ($type->type == 'object') {
                    $objectParser = new Parser\Object();
                    $objectParser->object = Migration\Object::get($type->object_id);
                    $objectParser->reader = $item;
                    $objectParser->parse();
                }
            } else {
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
    }

    function getInfo($item, $path)
    {
        $this->mapPath = Migration\Map\Path::get([
                    ['path', $path],
                    ['item', $item],
                    ['migration_map_id', $this->map->id]
        ]);
        if ($this->mapPath && $this->mapPath->type) {
            return $this->mapPath;
        }
        if (!$this->mapPath) {
            $this->mapPath = new Migration\Map\Path();
            $this->mapPath->parent_id = $this->mapPathParent ? $this->mapPathParent->id : 0;
            $this->mapPath->path = $path;
            $this->mapPath->item = $item;
            $this->mapPath->migration_map_id = $this->map->id;
            $this->mapPath->save();
        }
    }

}
