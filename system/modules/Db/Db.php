<?php

class Db extends Module
{

    public $connection = null;
    public $connect = false;

    function init($param = 'local')
    {
        if (!is_array($param)) {
            if (!($dbOption = Db\Options::get($param,'connect_alias',['array'=>true])))
                return false;

            $db = $dbOption;
        } else {
            $db = $param;
        }
        $className = 'Db\\'.$db['driver'];
        $this->connection = new $className();
        $this->connection->init($db);
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
