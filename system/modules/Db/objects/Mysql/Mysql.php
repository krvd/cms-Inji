<?php

/**
 * Работа с MySQL
 *
 * Класс для работы с базой данных MySQL
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 */

namespace Db;

class Mysql extends \Object {

    public $config = array();       // настройки подключения выбраной базы
    public $connect = FALSE;        // ярлык соединения с MySQL
    public $encoding = 'utf-8';        // установленная кодировка
    public $db_name = 'test';         // выбраная в данный момент база
    public $table_prefix = 'inji_';   // префикс названий таблиц
    public $where = '';             // актуальная строка условия
    public $cols = '*';             // актуальная строка столбцов
    public $order = NULL;           // актуальная строка выборки
    public $select_result = NULL;   // результат последнего запроса select
    public $result_array = array(); // массив из запроса select
    public $mysqli = NULL;
    public $join = '';
    public $group = NULL;
    public $limit = '';
    public $last_query = '';
    public $last_error = '';
    public $noConnectAbort = false;

    /**
     * Подключение к MySQL
     */
    public function init($connect_options) {
        extract($connect_options);
        if (isset($db_name))
            $this->db_name = $db_name;
        if (isset($encoding))
            $this->encoding = $encoding;
        if (isset($table_prefix))
            $this->table_prefix = $table_prefix;
        if (isset($noConnectAbort))
            $this->noConnectAbort = $noConnectAbort;
        $this->mysqli = @new \mysqli($host, $user, $pass, $db_name, $port);

        if ($this->mysqli->connect_error) {
            if ($this->noConnectAbort) {
                return false;
            } else {
                INJI_SYSTEM_ERROR($this->mysqli->connect_error, true);
            }
        } else {
            $this->mysqli->set_charset($this->encoding);
            if (defined('TIMEZONE')) {
                $this->setTimezone(TIMEZONE);
            }
            $this->connect = true;
            return true;
        }
    }

    /**
     * выбор базы данных
     */
    public function select_db($name) {
        $this->db_name = $this->mysqli->real_escape_string($name);
        return $this->mysqli->select_db($this->db_name);
    }

    /**
     * задание кодировки
     */
    public function set_names($enc) {
        $this->encoding = $this->mysqli->real_escape_string($enc);
        return $this->mysqli->set_charset($this->encoding);
    }

    /**
     * Установка временной зоны
     */
    public function setTimezone($timezone) {
        return $this->mysqli->query("SET timezone = '{$timezone}'");
    }

    /**
     * запрос на выборку
     */
    public function select($table, $noclean = false, $noprefix = false) {
        $table = $this->mysqli->real_escape_string($table);
        $query = "SELECT {$this->cols} FROM `{$this->db_name}`.`";
        if (!$noprefix)
            $query .= $this->table_prefix;
        $query .= "{$table}` {$this->join} {$this->where} {$this->group} {$this->order} {$this->limit}";
        $this->select_result = $this->query($query);

        if (!$noclean) {
            $this->where = '';
            $this->cols = '*';
            $this->order = '';
            $this->join = '';
            $this->group = '';
            $this->limit = '';
        }
        return $this->select_result;
    }

    /**
     * преобразование результата в массив
     */
    public function result_array($result, $index = false) {
        if (is_object($result))
            while ($row = $result->fetch_assoc())
                if ($index !== false)
                    $rows[$row[$index]] = $row;
                else
                    $rows[] = $row;

        if (isset($rows))
            return $rows;

        return array();
    }

    public function result_row($result) {
        if (is_object($result))
            if ($row = $result->fetch_assoc())
                return $row;

        return array();
    }

    /**
     * задает условие
     */
    function where($where = '', $value = '', $operation = false, $concatenation = 'AND') {
        if (!is_array($where) && !is_array($value) && !is_array($operation) && !is_array($concatenation)) {
            $where = $this->mysqli->real_escape_string($where);
            $value = $this->mysqli->real_escape_string($value);

            if (!$operation)
                $operation = '=';

            if ($concatenation === false)
                $concatenation = 'AND';
            elseif ($concatenation === true)
                $concatenation = '';

            if ($this->where == NULL)
                $this->where = ' WHERE ';

            if (stristr($operation, 'IN') || stristr($operation, 'NOT IN')) {
                if (!preg_match('!\(!', $value) && !preg_match('![^0-9,\.\(\) ]!', $value))
                    $value = "({$value})";
                elseif (preg_match('!\(!', $value) && preg_match('![^0-9,\.\(\) ]!', $value))
                    $value = "\"{$value}\"";
            }
            elseif (!in_array($value, array('CURRENT_TIMESTAMP'))) {
                $value = "\"{$value}\"";
            }

            if (substr($this->where, -1, 1) == '(' || substr($this->where, -2, 2) == 'E ')
                $this->where .= " `{$where}` {$operation} {$value} ";
            else
                $this->where .= "{$concatenation} `{$where}` {$operation} {$value} ";
        }
        else {
            $i = -1;
            while (isset($where[++$i])) {
                $item = $where[$i];
                if (isset($where[$i + 1]) && !isset($where[$i - 1]) && is_array($where[$i])) {
                    if ($this->where != NULL && substr($this->where, -1, 1) != '(' && $this->where != 'WHERE ') {
                        if (!isset($item[3]))
                            $concatenation = 'AND';
                        else
                            $concatenation = $item[3];

                        $this->where .= "{$concatenation} ";
                    }

                    if ($this->where != NULL)
                        $this->where .= '(';
                    else
                        $this->where = 'WHERE (';
                }

                if (!is_array($item)) {
                    call_user_func_array(array($this, 'where'), $where);
                    break;
                } else {
                    if ($this->where != NULL && substr($this->where, -1, 1) != '(')
                        if (!isset($item[3]))
                            $concatenation = 'AND';
                        else
                            $concatenation = $item[3];
                    elseif (substr($this->where, -1, 1) != '(')
                        $this->where = 'WHERE ';

                    $this->where($item);
                }
                if (!isset($where[$i + 1]) && isset($where[$i - 1]))
                    $this->where .= ') ';
            }
        }
    }

    /**
     * задает столбцы
     */
    public function cols($cols) {
        if (is_array($cols)) {
            $colsa = array();
            foreach ($cols as $item) {
                $item = $this->mysqli->real_escape_string($item);

                if (!preg_match('!`!', $item) && !preg_match('!\(!', $item) && !substr_count($item, '*'))
                    $item = "`{$item}`";
                $colsa[] = $item;
            }
            $this->cols = implode(',', $colsa);
        }
        else {
            $cols = $this->mysqli->real_escape_string($cols);

            if (!preg_match('!`!', $cols) && !preg_match('!\(!', $cols) && !substr_count($cols, '*'))
                $cols = "`{$cols}`";

            if ($this->cols == '*')
                $this->cols = $cols;
            else
                $this->cols .= ",{$item}";
        }
    }

    public function join($table, $where = false, $type = 'LEFT', $alias = '') {
        if (is_array($table)) {
            return call_user_func_array([$this, 'join'], $table);
        }
        $table = $this->mysqli->real_escape_string($table);

        $type = $this->mysqli->real_escape_string($type);
        $this->join .= " {$type} JOIN `{$this->table_prefix}{$table}` ";
        //var_dump($alias)
        if ($alias)
            $this->join .= "AS `{$alias}` ";
        if ($where)
            $this->join .= "ON {$where} ";
    }

    /**
     * задает сортирвку
     */
    public function limit($start = 0, $len = 0) {
        $start = intval($start);
        $len = intval($len);
        $this->limit = "LIMIT {$start}";
        if ($len !== 0)
            $this->limit .= ",{$len}";
    }

    public function order($order, $type = false) {
        if (!is_array($order)) {
            $order = $this->mysqli->real_escape_string($order);
            if (!preg_match('!\(!', $order))
                $order = "`{$order}`";

            $type = $this->mysqli->real_escape_string($type);

            if (!$type)
                $type = 'ASC';

            if ($this->order == NULL)
                $this->order = "ORDER BY {$order} {$type} ";
            else
                $this->order = "{$this->order}, {$order} {$type} ";
        }
        else {
            foreach ($order as $item)
                if (!is_array($item)) {
                    call_user_func_array(array($this, 'order'), $order);
                    break;
                } else
                    $this->order($item);
        }
    }

    /**
     * Выполнение запроса
     */
    function query($query) {
        $key = App::$cur->Log->start('query: ' . $query);
        $result = $this->mysqli->query($query);
        App::$cur->Log->end($key);
        $this->last_query = $query;
        $this->last_error = $this->mysqli->error;
        if ($this->last_error) {
            App::$cur->Log->event('error last query: ' . $this->last_error, 'danger');
        }

        return $result;
    }

    function group($colname) {
        if ($this->group == NULL)
            $this->group = "GROUP BY `{$colname}`";
        else
            $this->group = "{$this->group}, `{$colname}`";
    }

    function insert($table, $data) {
        $table = $this->mysqli->real_escape_string($table);

        foreach ($data as $key => $item)
            $col_keys[] = $this->mysqli->real_escape_string($key);

        foreach ($data as $key => $item)
            $datas[$key] = $this->mysqli->real_escape_string($item);

        $cols = implode('`,`', $col_keys);
        $vals = implode("','", $datas);
        $this->query("INSERT INTO `{$this->table_prefix}{$table}` (`{$cols}`) VALUES ('{$vals}')");
        return $this->mysqli->insert_id;
    }

    function delete($table, $noclean = 0) {
        $table = $this->mysqli->real_escape_string($table);
        $this->query("DELETE FROM `{$this->table_prefix}{$table}` {$this->where}");
        if ($noclean == 0)
            $this->where = '';

        return $this->mysqli->affected_rows;
    }

    function update($table, $data, $noclean = 0) {
        $table = $this->mysqli->real_escape_string($table);

        foreach ($data as $key => $item) {
            $key = $this->mysqli->real_escape_string($key);
            $item = $this->mysqli->real_escape_string($item);
            if (!in_array($item, array('CURRENT_TIMESTAMP')))
                $updates[] = "`{$key}` = '{$item}'";
            else
                $updates[] = "`{$key}` = {$item}";
        }

        $update = implode(',', $updates);
        $this->query("UPDATE `{$this->table_prefix}{$table}` SET {$update} {$this->where}");

        if ($noclean == 0)
            $this->where = '';

        return $this->mysqli->affected_rows;
    }

    function getTableCols($table_name) {
        $old_db = $this->db_name;
        $this->select_db('information_schema');

        $this->where('TABLE_SCHEMA', $old_db);
        $this->where('TABLE_NAME', $this->table_prefix . $table_name);
        $result = $this->select('COLUMNS', false, true);

        $cols = $this->result_array($result, 'COLUMN_NAME');

        $this->select_db($old_db);
        return $cols;
    }

    function add_col($table = false, $name = false, $param = 'TEXT NOT NULL') {
        if (!$table || !$name)
            return false;

        return $this->query("ALTER TABLE `{$this->table_prefix}{$table}` ADD `{$name}` {$param}");
    }

    function del_col($table = false, $name = false) {
        if (!$table || !$name)
            return false;

        return $this->query("ALTER TABLE `{$this->table_prefix}{$table}` DROP `{$name}`");
    }

    function create_table($table_name, $cols, $indexes = []) {
        $query = "CREATE TABLE  `{$this->db_name}`.`{$this->table_prefix}{$table_name}` (";
        foreach ($cols as $col_name => $param) {
            if ($param == 'pk')
                $param = "int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (`{$col_name}`)";
            $query .= " `{$col_name}` {$param},";
        }

        $query = rtrim($query, ',');
        $query .= ' ' . implode(',', $indexes);
        $query .= ") ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci";
        $this->query($query);
    }

}
