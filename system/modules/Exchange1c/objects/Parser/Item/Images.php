<?php

/**
 * Item images parser
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2016 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Exchange1c\Parser\Item;

class Images extends \Migrations\Parser
{
    public function parse()
    {
        $value = (string) $this->reader;
        $notEq = true;
        $dir = pathinfo($this->reader->source, PATHINFO_DIRNAME);
        foreach ($this->object->model->images as $image) {
            $file = $image->file;
            if ($file && $value && file_exists($dir . '/' . $value) && file_exists(\App::$primary->path . $file->path) && md5_file($dir . '/' . $value) == md5_file(\App::$primary->path . $file->path)) {
                $notEq = false;
            }
        }
        if ($notEq) {
            $file_id = \App::$primary->files->uploadFromUrl($dir . '/' . $value, ['accept_group' => 'image', 'upload_code' => 'MigrationUpload']);
            $image = new \Ecommerce\Item\Image([
                'item_id' => $this->object->model->pk(),
                'file_id' => $file_id
            ]);
            $image->save();
        }
        if ($image && !$this->object->model->image_file_id) {
            $this->object->model->image_file_id = $image->file_id;
        }
    }

}
