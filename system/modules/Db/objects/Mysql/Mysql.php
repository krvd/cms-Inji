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
    public $select_result = NULL;   // результат последнего запроса select
    public $result_array = array(); // массив из запроса select
    public $pdo = NULL;
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

        $dsn = "mysql:host=$host;port=$port;dbname=$db_name;charset=$encoding";
        $opt = array(
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
        );
        $this->pdo = new \PDO($dsn, $user, $pass, $opt);
        $error = $this->pdo->errorInfo();

        if ($error[0]) {
            if ($this->noConnectAbort) {
                return false;
            } else {

                INJI_SYSTEM_ERROR($error[2], true);
            }
        } else {
            $this->connect = true;
            return true;
        }
    }

    function getTableCols($table_name) {
        $query = new Mysql\Query();
        $old_db = $this->db_name;
        $this->db_name = 'information_schema';

        $query->where('TABLE_SCHEMA', $old_db);
        $query->where('TABLE_NAME', $this->table_prefix . $table_name);
        $result = $query->select('COLUMNS');
        $this->db_name = $old_db;
        return $result->getArray('COLUMN_NAME');
        
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

}
