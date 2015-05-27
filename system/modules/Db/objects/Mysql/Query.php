<?php

/**
 * Query class for mysql driver
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Db\Mysql;

class Query extends \Object {

    public $curInstance = null;
    public $where = '';             // актуальная строка условия
    public $cols = '*';             // актуальная строка столбцов
    public $order = NULL;           // актуальная строка выборки
    public $join = '';
    public $group = NULL;
    public $limit = '';
    public $error = '';
    public $query = '';
    public $table = '';
    public $operation = '';

    function __construct($instance = null) {
        if (!$instance) {
            $this->curInstance = \App::$cur->db->connection;
        }
        else{
            $this->curInstance=$instance;
        }
    }

    /**
     * запрос на выборку
     */
    public function select($table) {
        $this->operation = 'SELECT';
        $this->table = $table;
        $query = $this->buildQuery();
        var_dump($query);
        $prepare = $this->curInstance->pdo->prepare($query['query'],$query['params']);
        $result = $prepare->execute();
        echo $prepare->queryString;
        
        //return $this->select_result;
    }
    
    function buildQuery(){
        $params = [];
        $query = $this->operation;
        
        $query .= ' '.$this->cols;
        
        $query .= " FROM `{$this->curInstance->db_name}`.`{$this->curInstance->table_prefix}{$this->table}`";
        return compact('query','params');
    }
}
