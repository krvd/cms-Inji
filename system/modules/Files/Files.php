<?php

class Files extends Module {

    /**
     * Загрузка файлов
     * 
     * $file - масив из переменной $_FILES[{input name}]
     * $options - массив из опций заливки 
     * --	[file_code]: уникальный код для системы медиаданых
     * --	[allow_types]: досупные для заливки типы файлов. Например image (тип форматов из таблицы типов файлов file_type_ext)
     */
    function upload($file, $options = array()) {

        $site_path = App::$primary->path;

        if (!is_uploaded_file($file['tmp_name']))
            return false;

        $fileinfo = pathinfo($file['name']);
        if (empty($fileinfo['extension']))
            return false;

        $type = Files\Type::get($fileinfo['extension'], 'ext');
        if (!$type)
            return false;

        $fileObject = new Files\File();
        if (!empty($options['file_code'])) {
            $fileObject = Files\File::get($options['file_code'], 'file_code');
        } else {
            $fileObject->name = microtime(true);
        }

        $fileObject->path = $type->type_dir . date('Y-m-d') . '/' . $fileObject->name . '.' . $fileinfo['extension'];
        if ($fileObject->id && file_exists($site_path .$fileObject->path))
            unlink($site_path .$fileObject->path);

        Tools::createDir($site_path . $type->type_dir . date('Y-m-d') . '/');

        if (!move_uploaded_file($file['tmp_name'], $site_path . $fileObject->path))
            return false;
        
        $fileObject->type_id = $type->pk();
        $fileObject->original_name = $file['name'];
        $fileObject->date_create = 'CURRENT_TIMESTAMP';
        $fileObject->save();

        return $fileObject->id;
    }

    /**
     * Загрузка файлов по урл
     * 
     * $url - адрес файла
     * $options - массив из опций заливки 
     * --	[file_code]: уникальный код для системы медиаданых
     * --	[allow_types]: досупные для заливки типы файлов. Например image (тип форматов из таблицы типов файлов file_type_ext)
     */
    function upload_from_url($url, $options = array()) {
        $fileinfo = pathinfo($url);
        /*
          'dirname' => string '/userfiles' (length=10)
          'basename' => string 'avtokran(1).gif' (length=15)
          'extension' => string 'gif' (length=3)
          'filename' => string 'avtokran(1)' (length=11)
         */
        if (App::$cur->app['system'])
            $site_path = App::$cur->app['parent']['path'];
        else
            $site_path = App::$cur->app['path'];
        Tools::createDir($site_path . '/static/tmp/');
        $file = file_get_contents($url);
        file_put_contents($site_path . '/static/tmp/' . $fileinfo['basename'], $file);
        /* if (!copy($url, App::$cur->app['path'] . '/static/tmp/' . $fileinfo['basename']))
          return false; */

        if (empty($fileinfo['extension']))
            return false;

        $type = $this->get_type_by_ext($fileinfo['extension']);
        if (!$type)
            return false;

        $cur_file = array();
        if (!empty($options['file_code'])) {
            $name = $options['file_code'];
            $cur_file = $this->get_by_code($options['file_code']);
        } else
            $name = microtime(true);

        $path = $type['file_type_dir'] . date('Y-m-d') . '/' . $name . '.' . $fileinfo['extension'];
        if (!empty($options['file_code']) && file_exists($path))
            unlink($path);

        Tools::createDir($site_path . $type['file_type_dir'] . date('Y-m-d') . '/');

        if (!copy($site_path . '/static/tmp/' . $fileinfo['basename'], $site_path . $path))
            return false;
        unlink($site_path . '/static/tmp/' . $fileinfo['basename']);
        if ($cur_file) {
            $file_id = $cur_file['file_id'];
            $this->update($file_id, array(
                'file_path' => $path,
                'file_type' => $type['file_type_id'],
                'file_name' => $fileinfo['filename'],
                'file_original_name' => $fileinfo['basename'],
                'file_date_create' => 'CURRENT_TIMESTAMP'
            ));
        } else {
            $ins = array();
            $ins['file_path'] = $path;
            $ins['file_type'] = $type['file_type_id'];
            if (!empty($options['file_code']))
                $ins['file_code'] = $options['file_code'];
            $ins['file_name'] = $fileinfo['filename'];
            $ins['file_original_name'] = $fileinfo['basename'];
            //$ins['file_date_create'] = 'CURRENT_TIMESTAMP';
            $file_id = $this->add($ins);
        }
        return $file_id;
    }

}

?>
