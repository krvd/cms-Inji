<?php

class Db extends Module
{

    public $connection = null;
    public $connect = false;
    public $dbConfig = [];
    public $curQuery = null;

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
        $this->dbConfig = $db;
    }

    function __call($name, $params)
    {
        if(!is_object($this->connection)){
            return false;
        }
        $className = 'Db\\'.$this->dbConfig['driver'];
        $QueryClassName = 'Db\\'.$this->dbConfig['driver'].'\\Query';
        $ResultClassName = 'Db\\'.$this->dbConfig['driver'].'\\Result';
        if(method_exists($className, $name)){
            
                return call_user_func_array(array($this->connection, $name), $params);
            
        }
        if(method_exists($QueryClassName, $name)){
            if(!is_object($this->curQuery)){
                $this->curQuery = new Db\Mysql\Query($this->connection);
            }
            return call_user_func_array(array($this->curQuery, $name), $params);
        }
            
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
