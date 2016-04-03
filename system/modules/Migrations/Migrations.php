<?php

/**
 * Data Migrations class
 *
 * Migration from file, to file, from web, to web
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class Migrations extends \Module
{
    public function startMigration($migrationId, $mapId, $filePath)
    {
        $log = new \Migrations\Log();
        $log->migration_id = $migrationId;
        $log->migration_map_id = $mapId;
        $log->source = $filePath;
        $log->save();

        $reader = new Migrations\Reader\Xml();
        if (!$reader->loadData($filePath)) {
            $event = new Migrations\Log\Event();
            $event->log_id = $log->id;
            $event->type = 'load_data_error';
            $event->save();
            return false;
        }
        $walker = new \Migrations\Walker();
        $walker->migration = Migrations\Migration::get($migrationId);
        $walker->map = Migrations\Migration\Map::get($mapId);
        $walker->data = $reader->getArray();
        $walker->migtarionLog = $log;
        $walker->walk();
        $log->result = 'success';
        $log->save();
    }

}
