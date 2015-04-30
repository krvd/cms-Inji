<?php

class Db extends Module
{

    public $connection = null;
    public $connect = false;

    function init($param = 'local')
    {
        if (!is_array($param)) {
            if (!isset($this->config['databases'][$param]))
                return false;

            $db = $this->config['databases'][$param];
        } else {
            $db = $param;
        }
        $path = INJI_SYSTEM_DIR . '/drivers/' . $db['connect_driver'] . '/' . $db['connect_driver'] . 'Driver.php';
        if(!file_exists($path)){
            INJI_SYSTEM_ERROR('driver not found', true);
        }
        include_once $path;
        $className =  $db['connect_driver'] . 'Driver';
        $this->connection = new $className();
        $this->connection->init($db['connect_options']);
        $this->connect = $this->connection->connect;
    }

    function __call($name, $params)
    {
        if (is_object($this->connection))
            return call_user_func_array(array($this->connection, $name), $params);
        return false;
    }

    function __get($name)
    {
        return $this->connection->$name;
    }
    function __set($name, $value)
    {
        return $this->connection->$name = $value;
    }

}
