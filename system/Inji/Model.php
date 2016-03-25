<?php

/**
 * Log
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class Model
{
    /**
     * Object storage type
     * 
     * @var array 
     */
    public static $storage = ['type' => 'db'];

    /**
     * Object name
     * 
     * @var string 
     */
    public static $objectName = '';

    /**
     * App type for separate data storage
     * 
     * @var string
     */
    public $appType = 'app';

    /**
     * Object current params
     * 
     * @var array
     */
    public $_params = [];

    /**
     * List of changed params in current instance
     * 
     * @var array
     */
    public $_changedParams = [];

    /**
     * Loaded relations
     * 
     * @var array 
     */
    public $loadedRelations = [];

    /**
     * Model name where this model uses as category
     * 
     * @var string
     */
    public static $treeCategory = '';

    /**
     * Model name who uses as category in this model
     * 
     * @var string
     */
    public static $categoryModel = '';

    /**
     * Col labels
     * 
     * @var array
     */
    public static $labels = [];

    /**
     * Model forms
     * 
     * @var array
     */
    public static $forms = [];

    /**
     * Model cols
     * 
     * @var array
     */
    public static $cols = [];

    /**
     * Options group for display inforamtion from model
     * 
     * @var array
     */
    public static $view = [];

    /**
     * List of relations need loaded with item
     * 
     * @var array 
     */
    public static $needJoin = [];

    /**
     * List of joins who need to laod
     * 
     * @var array 
     */
    public static $relJoins = [];

    /**
     * Set params when model create
     * 
     * @param array $params
     */
    public function __construct($params = [])
    {
        $this->setParams($params);
    }

    /**
     * return object name
     * 
     * @return string
     */
    public static function objectName()
    {
        return static::$objectName;
    }

    /**
     * Retrn col value with col params and relations path
     * 
     * @param Model $object
     * @param string $valuePath
     * @param boolean $convert
     * @param boolean $manageHref
     * @return string
     */
    public static function getColValue($object, $valuePath, $convert = false, $manageHref = false)
    {
        if (strpos($valuePath, ':')) {
            $rel = substr($valuePath, 0, strpos($valuePath, ':'));
            $param = substr($valuePath, strpos($valuePath, ':') + 1);
            if (!$object->$rel) {
                $modelName = get_class($object);
                $relations = $modelName::relations();
                if (empty($relations[$rel]['type']) || $relations[$rel]['type'] == 'one') {
                    return $object->{$relations[$rel]['col']};
                }
                return 0;
            }
            if (strpos($valuePath, ':')) {
                return self::getColValue($object->$rel, $param, $convert, $manageHref);
            } else {
                return $convert ? Model::resloveTypeValue($object->$rel, $param, $manageHref) : $object->$rel->$param;
            }
        } else {
            return $convert ? Model::resloveTypeValue($object, $valuePath, $manageHref) : $object->$valuePath;
        }
    }

    /**
     * Retrun value for view
     * 
     * @param Model $item
     * @param string $colName
     * @param boolean $manageHref
     * @return string
     */
    public static function resloveTypeValue($item, $colName, $manageHref = false)
    {
        $modelName = get_class($item);
        $colInfo = $modelName::getColInfo($colName);
        $type = !empty($colInfo['colParams']['type']) ? $colInfo['colParams']['type'] : 'string';
        $value = '';
        switch ($type) {
            case 'select':
                switch ($colInfo['colParams']['source']) {
                    case 'model':
                        $sourceValue = '';
                        if ($item->$colName) {
                            $sourceValue = $colInfo['colParams']['model']::get($item->$colName);
                        }
                        $value = $sourceValue ? $sourceValue->name() : 'Не задано';
                        break;
                    case 'array':
                        $value = !empty($colInfo['colParams']['sourceArray'][$item->$colName]) ? $colInfo['colParams']['sourceArray'][$item->$colName] : 'Не задано';
                        if (is_array($value) && $value['text']) {
                            $value = $value['text'];
                        }
                        break;
                    case 'bool':
                        return $item->$colName ? 'Да' : 'Нет';
                    case 'method':
                        if (!empty($colInfo['colParams']['params'])) {
                            $values = call_user_func_array([App::$cur->$colInfo['colParams']['module'], $colInfo['colParams']['method']], $colInfo['colParams']['params']);
                        } else {
                            $values = $colInfo['colParams']['module']->$colInfo['colParams']['method']();
                        }
                        $value = !empty($values[$item->$colName]) ? $values[$item->$colName] : 'Не задано';
                        break;
                    case 'void':
                        if (!empty($modelName::$cols[$colName]['value']['type']) && $modelName::$cols[$colName]['value']['type'] == 'moduleMethod') {
                            return \App::$cur->{$modelName::$cols[$colName]['value']['module']}->{$modelName::$cols[$colName]['value']['method']}($item, $colName, $modelName::$cols[$colName]);
                        }
                        break;
                    case 'relation':
                        $relations = $colInfo['modelName']::relations();
                        $relValue = $relations[$colInfo['colParams']['relation']]['model']::get($item->$colName);
                        $relModel = $relations[$colInfo['colParams']['relation']]['model'];
                        $relModel = strpos($relModel, '\\') === 0 ? substr($relModel, 1) : $relModel;
                        if ($manageHref) {
                            $value = $relValue ? "<a href='/admin/" . str_replace('\\', '/view/', $relModel) . "/" . $relValue->pk() . "'>" . $relValue->name() . "</a>" : 'Не задано';
                        } else {
                            $value = $relValue ? $relValue->name() : 'Не задано';
                        }
                        break;
                }
                break;
            case 'image':
                $file = Files\File::get($item->$colName);
                if ($file) {
                    $value = '<img src="' . $file->path . '?resize=60x120" />';
                } else {
                    $value = '<img src="/static/system/images/no-image.png?resize=60x120" />';
                }
                break;
            case 'bool':
                $value = $item->$colName ? 'Да' : 'Нет';
                break;
            case 'void':
                if (!empty($colInfo['colParams']['value']['type']) && $colInfo['colParams']['value']['type'] == 'moduleMethod') {
                    return \App::$cur->{$colInfo['colParams']['value']['module']}->{$colInfo['colParams']['value']['method']}($item, $colName, $colInfo['colParams']);
                }
                break;
            default:
                $value = $item->$colName;
                break;
        }
        return $value;
    }

    /**
     * Fix col prefix
     * 
     * @param mixed $array
     * @param string $searchtype
     * @param string $rootModel
     * @return null
     */
    public static function fixPrefix(&$array, $searchtype = 'key', $rootModel = '')
    {
        if (!$rootModel) {
            $rootModel = get_called_class();
        }
        $cols = static::cols();
        if (!$array) {
            return;
        }
        if (!is_array($array)) {
            if (!isset($cols[static::colPrefix() . $array]) && isset(static::$cols[$array])) {
                static::createCol($array);
                $cols = static::cols(true);
            }
            if (!isset($cols[$array]) && isset($cols[static::colPrefix() . $array])) {
                $array = static::colPrefix() . $array;
            } else {
                static::checkForJoin($array, $rootModel);
            }
            return;
        }
        switch ($searchtype) {
            case 'key':
                foreach ($array as $key => $item) {
                    if (!isset($cols[static::colPrefix() . $key]) && isset(static::$cols[$key])) {
                        static::createCol($key);
                        $cols = static::cols(true);
                    }
                    if (!isset($cols[$key]) && isset($cols[static::colPrefix() . $key])) {
                        $array[static::colPrefix() . $key] = $item;
                        unset($array[$key]);
                        $key = static::colPrefix() . $key;
                    }
                    if (is_array($array[$key])) {
                        static::fixPrefix($array[$key], 'key', $rootModel);
                    } else {
                        static::checkForJoin($key, $rootModel);
                    }
                }
                break;
            case 'first':
                if (isset($array[0]) && is_string($array[0])) {
                    if (!isset($cols[static::colPrefix() . $array[0]]) && isset(static::$cols[$array[0]])) {
                        static::createCol($array[0]);
                        $cols = static::cols(true);
                    }
                    if (!isset($cols[$array[0]]) && isset($cols[static::colPrefix() . $array[0]])) {
                        $array[0] = static::colPrefix() . $array[0];
                    } else {
                        static::checkForJoin($array[0], $rootModel);
                    }
                } elseif (isset($array[0]) && is_array($array[0])) {
                    foreach ($array as &$item) {
                        static::fixPrefix($item, 'first', $rootModel);
                    }
                }
                break;
        }
    }

    /**
     * Check model relations path and load need relations
     * 
     * @param string $col
     * @param string $rootModel
     */
    public static function checkForJoin(&$col, $rootModel)
    {

        if (strpos($col, ':') !== false) {
            $relations = static::relations();
            if (isset($relations[substr($col, 0, strpos($col, ':'))])) {
                $rel = substr($col, 0, strpos($col, ':'));
                $col = substr($col, strpos($col, ':') + 1);

                $type = empty($relations[$rel]['type']) ? 'to' : $relations[$rel]['type'];
                switch ($type) {
                    case 'to':
                        $relCol = $relations[$rel]['col'];
                        static::fixPrefix($relCol);
                        $rootModel::$relJoins[$relations[$rel]['model'] . '_' . $rel] = [$relations[$rel]['model']::table(), $relations[$rel]['model']::index() . ' = ' . $relCol];
                        break;
                    case 'one':
                    case 'many':
                        $relCol = $relations[$rel]['col'];
                        $relations[$rel]['model']::fixPrefix($relCol);
                        $rootModel::$relJoins[$relations[$rel]['model'] . '_' . $rel] = [$relations[$rel]['model']::table(), static::index() . ' = ' . $relCol];
                        break;
                }
                $relations[$rel]['model']::fixPrefix($col, 'key', $rootModel);
            }
        }
    }

    /**
     * Return full col information
     * 
     * @param string $col
     * @return array
     */
    public static function getColInfo($col)
    {
        return static::parseColRecursion($col);
    }

    /**
     * Information extractor for col relations path
     * 
     * @param string|array $info
     * @return array
     */
    public static function parseColRecursion($info)
    {
        if (is_string($info)) {
            $info = ['col' => $info, 'rawCol' => $info, 'modelName' => '', 'label' => [], 'joins' => []];
        }
        if (strpos($info['col'], ':') !== false) {
            $relations = static::relations();
            if (isset($relations[substr($info['col'], 0, strpos($info['col'], ':'))])) {
                $rel = substr($info['col'], 0, strpos($info['col'], ':'));
                $info['col'] = substr($info['col'], strpos($info['col'], ':') + 1);
                $type = empty($relations[$rel]['type']) ? 'to' : $relations[$rel]['type'];
                switch ($type) {
                    case 'to':
                        $relCol = $relations[$rel]['col'];
                        static::fixPrefix($relCol);
                        //$info['modelName'] = $relations[$rel]['model'];
                        $info['joins'][$relations[$rel]['model'] . '_' . $rel] = [$relations[$rel]['model']::table(), $relations[$rel]['model']::index() . ' = ' . $relCol];
                        break;
                    case 'one':
                        $relCol = $relations[$rel]['col'];
                        $relations[$rel]['model']::fixPrefix($relCol);
                        //$info['modelName'] = $relations[$rel]['model'];
                        $info['joins'][$relations[$rel]['model'] . '_' . $rel] = [$relations[$rel]['model']::table(), static::index() . ' = ' . $relCol];
                        break;
                }
                $info = $relations[$rel]['model']::parseColRecursion($info);
            }
        } else {
            $cols = static::cols();
            if (!empty(static::$labels[$info['col']])) {
                $info['label'] = static::$labels[$info['col']];
            }

            if (isset(static::$cols[$info['col']])) {
                $info['colParams'] = static::$cols[$info['col']];
            } elseif (isset(static::$cols[str_replace(static::colPrefix(), '', $info['col'])])) {
                $info['colParams'] = static::$cols[str_replace(static::colPrefix(), '', $info['col'])];
            } else {
                $info['colParams'] = [];
            }
            if (!isset($cols[$info['col']]) && isset($cols[static::colPrefix() . $info['col']])) {
                $info['col'] = static::colPrefix() . $info['col'];
            }
            $info['modelName'] = get_called_class();
        }
        return $info;
    }

    /**
     * Return actual cols from data base
     * 
     * @param boolean $refresh
     * @return array
     */
    public static function cols($refresh = false)
    {
        if (static::$storage['type'] == 'moduleConfig') {
            return [];
        }
        if (empty(Model::$cols[static::table()]) || $refresh) {
            Model::$cols[static::table()] = App::$cur->db->getTableCols(static::table());
        }
        if (!Model::$cols[static::table()]) {
            static::createTable();
            Model::$cols[static::table()] = App::$cur->db->getTableCols(static::table());
        }
        return Model::$cols[static::table()];
    }

    /**
     * Return cols indexes for create tables
     * 
     * @return array
     */
    public static function indexes()
    {
        return [];
    }

    /**
     * Generate params string for col by name
     * 
     * @param string $colName
     * @return boolean|string
     */
    public static function genColParams($colName)
    {
        if (empty(static::$cols[$colName]) || static::$storage['type'] == 'moduleConfig') {
            return false;
        }

        $params = false;
        switch (static::$cols[$colName]['type']) {
            case 'select':
                switch (static::$cols[$colName]['source']) {
                    case 'relation':
                        $params = 'int(11) UNSIGNED NOT NULL';
                        break;
                    default:
                        $params = 'varchar(255) NOT NULL';
                }
                break;
            case 'image':
                $params = 'int(11) UNSIGNED NOT NULL';
                break;
            case 'number':
                $params = 'int(11) NOT NULL';
                break;
            case 'text':
            case 'email':
                $params = 'varchar(255) NOT NULL';
                break;
            case 'html':
            case 'textarea':
            case 'json':
            case 'password':
            case 'dynamicType':
                $params = 'text NOT NULL';
                break;
            case 'bool':
                $params = 'tinyint(1) UNSIGNED NOT NULL';
                break;
            case 'decimal':
                $params = 'decimal(8, 2) NOT NULL';
                break;
            case 'date':
                $params = 'date NOT NULL DEFAULT 0';
                break;
            case 'dateTime':
                $params = 'timestamp NOT NULL DEFAULT 0';
                break;
        }
        return $params;
    }

    /**
     * Create new col in data base
     * 
     * @param string $colName
     * @return boolean|integer
     */
    public static function createCol($colName)
    {
        $params = static::genColParams($colName);
        if ($params === false) {
            return false;
        }
        return App::$cur->db->addCol(static::table(), static::colPrefix() . $colName, $params);
    }

    public static function createTable()
    {
        if (static::$storage['type'] == 'moduleConfig') {
            return true;
        }
        if (!App::$cur->db) {
            return false;
        }

        $query = App::$cur->db->newQuery();
        if (!$query) {
            return false;
        }

        if (!isset($this)) {
            $tableName = static::table();
            $colPrefix = static::colPrefix();
            $indexes = static::indexes();
        } else {
            $tableName = $this->table();
            $colPrefix = $this->colPrefix();
            $indexes = $this->indexes();
        }
        if (App::$cur->db->tableExist($tableName)) {
            return true;
        }
        $cols = [
            $colPrefix . 'id' => 'pk'
        ];
        $className = get_called_class();
        if (!empty($className::$cols)) {
            foreach ($className::$cols as $colName => $colParams) {
                if ($colName == 'date_create') {
                    $cols[$colPrefix . 'date_create'] = 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP';
                    continue;
                }
                $params = $className::genColParams($colName);
                if ($params) {
                    $cols[$colPrefix . $colName] = $params;
                }
            }
        }
        if (empty($cols[$colPrefix . 'date_create'])) {
            $cols[$colPrefix . 'date_create'] = 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP';
        }
        $tableIndexes = [];
        if ($indexes) {
            foreach ($indexes as $indexName => $index) {
                $tableIndexes[] = $index['type'] . ' ' . App::$cur->db->table_prefix . $indexName . ' (' . implode(',', $index['cols']) . ')';
            }
        }

        $query->createTable($tableName, $cols, $tableIndexes);
        return true;
    }

    /**
     * Return table name
     * 
     * @return string
     */
    public static function table()
    {
        return strtolower(str_replace('\\', '_', get_called_class()));
    }

    /**
     * Return table index col name
     * 
     * @return string
     */
    public static function index()
    {

        return static::colPrefix() . 'id';
    }

    /**
     * Return col prefix
     * 
     * @return string
     */
    public static function colPrefix()
    {
        $classPath = explode('\\', get_called_class());
        $classPath = array_slice($classPath, 1);
        return strtolower(implode('_', $classPath)) . '_';
    }

    /**
     * return relations list
     * 
     * @return array
     */
    public static function relations()
    {
        return [];
    }

    /**
     * Return name of col with object name
     * 
     * @return string
     */
    public static function nameCol()
    {
        return 'name';
    }

    /**
     * Return object name
     * 
     * @return string
     */
    public function name()
    {
        return $this->{$this->nameCol()} ? $this->{$this->nameCol()} : $this->pk();
    }

    /**
     * Get single object from data base
     * 
     * @param mixed $param
     * @param string $col
     * @param array $options
     * @return boolean|\Model
     */
    public static function get($param, $col = null, $options = [])
    {
        if (static::$storage['type'] == 'moduleConfig') {
            return static::getFromModuleStorage($param, $col, $options);
        }
        if (!empty($col)) {
            static::fixPrefix($col);
        }

        if (is_array($param)) {
            static::fixPrefix($param, 'first');
        }
        foreach (static::$relJoins as $join) {
            App::$cur->db->join($join[0], $join[1]);
        }
        static::$relJoins = [];
        foreach (static::$needJoin as $rel) {
            $relations = static::relations();
            if (isset($relations[$rel])) {
                $type = empty($relations[$rel]['type']) ? 'to' : $relations[$rel]['type'];
                switch ($type) {
                    case 'to':
                        $relCol = $relations[$rel]['col'];
                        static::fixPrefix($relCol);
                        App::$cur->db->join($relations[$rel]['model']::table(), $relations[$rel]['model']::index() . ' = ' . $relCol);
                        break;
                    case 'one':
                        $col = $relations[$rel]['col'];
                        $relations[$rel]['model']::fixPrefix($col);
                        App::$cur->db->join($relations[$rel]['model']::table(), static::index() . ' = ' . $col);
                        break;
                }
            }
        }
        static::$needJoin = [];
        if (is_array($param)) {
            App::$cur->db->where($param);
        } else {
            if ($col === null) {

                $col = static::index();
            }
            if ($param !== null) {
                $cols = static::cols();
                if (!isset($cols[$col]) && isset($cols[static::colPrefix() . $col])) {
                    $col = static::colPrefix() . $col;
                }
                App::$cur->db->where($col, $param);
            } else {
                return false;
            }
        }
        if (!App::$cur->db->where) {
            return false;
        }
        try {
            $result = App::$cur->db->select(static::table());
        } catch (PDOException $exc) {
            if ($exc->getCode() == '42S02') {
                static::createTable();
            }
            $result = App::$cur->db->select(static::table());
        }
        if (!$result) {
            return false;
        }
        return $result->fetch(get_called_class());
    }

    /**
     * Old method
     * 
     * @param type $options
     * @return Array
     */
    public static function get_list($options = [])
    {
        $query = App::$cur->db->newQuery();
        if (!$query) {
            return [];
        }
        if (!empty($options['where']))
            $query->where($options['where']);
        if (!empty($options['group'])) {
            $query->group($options['group']);
        }
        if (!empty($options['order']))
            $query->order($options['order']);
        if (!empty($options['join']))
            $query->join($options['join']);
        if (!empty($options['distinct']))
            $query->distinct = $options['distinct'];

        foreach (static::$relJoins as $join) {
            $query->join($join[0], $join[1]);
        }
        static::$relJoins = [];
        foreach (static::$needJoin as $rel) {
            $relations = static::relations();
            if (isset($relations[$rel])) {
                $type = empty($relations[$rel]['type']) ? 'to' : $relations[$rel]['type'];
                switch ($type) {
                    case 'to':
                        $relCol = $relations[$rel]['col'];
                        static::fixPrefix($relCol);
                        $query->join($relations[$rel]['model']::table(), $relations[$rel]['model']::index() . ' = ' . $relCol);
                        break;
                    case 'one':
                        $col = $relations[$rel]['col'];
                        $relations[$rel]['model']::fixPrefix($col);
                        $query->join($relations[$rel]['model']::table(), static::index() . ' = ' . $col);
                        break;
                }
            }
        }
        static::$needJoin = [];

        if (!empty($options['limit']))
            $limit = (int) $options['limit'];
        else {
            $limit = 0;
        }
        if (!empty($options['start']))
            $start = (int) $options['start'];
        else {
            $start = 0;
        }
        if ($limit || $start) {
            $query->limit($start, $limit);
        }
        if (isset($options['key'])) {
            $key = $options['key'];
        } else {
            $key = static::index();
        }
        try {
            $query->operation = 'SELECT';
            $query->table = static::table();
            $queryArr = $query->buildQuery();
            $result = $query->query($queryArr);
        } catch (PDOException $exc) {
            if ($exc->getCode() == '42S02') {
                static::createTable();
                $result = $query->query($queryArr);
            }
            else {
                throw $exc;
            }
            
        }

        if (!empty($options['array'])) {
            return $result->getArray($key);
        }
        $list = $result->getObjects(get_called_class(), $key);
        if (!empty($options['forSelect'])) {
            $return = [];
            foreach ($list as $key => $item) {
                $return[$key] = $item->name();
            }
            return $return;
        }
        return $list;
    }

    /**
     * Return list of objects from data base
     * 
     * @param type $options
     * @return type
     */
    public static function getList($options = [])
    {
        if (static::$storage['type'] != 'db') {
            return static::getListFromModuleStorage($options);
        }
        if (!empty($options['where'])) {
            static::fixPrefix($options['where'], 'first');
        }
        if (!empty($options['order'])) {
            static::fixPrefix($options['order'], 'first');
        }
        return static::get_list($options);
    }

    /**
     * Get single item from module storage
     * 
     * @param array $param
     * @param string $col
     * @param array $options
     * @return boolean|\Model
     */
    public static function getFromModuleStorage($param = null, $col = null, $options = [])
    {
        if ($col === null) {

            $col = static::index();
        }
        if ($param == null) {
            return false;
        }
        $classPath = explode('\\', get_called_class());
        if (!empty(static::$storage['options']['share'])) {
            $moduleConfig = Config::share($classPath[0]);
        } else {
            $moduleConfig = Config::module($classPath[0], strpos(static::$storage['type'], 'system') !== false);
        }
        $appType = App::$cur->type;
        if (!empty($moduleConfig['storage']['appTypeSplit'])) {
            if (!empty($options['appType'])) {
                $appType = $options['appType'];
            }
            $storage = !empty($moduleConfig['storage'][$appType]) ? $moduleConfig['storage'][$appType] : [];
        } else {
            $storage = !empty($moduleConfig['storage']) ? $moduleConfig['storage'] : [];
        }
        if (!empty($storage[$classPath[1]])) {
            $items = $storage[$classPath[1]];
            $class = get_called_class();
            foreach ($items as $key => $item) {
                if ($item[$col] == $param) {
                    if (!empty($options['array'])) {
                        return $item;
                    }
                    $item = new $class($item);
                    $item->appType = $appType;
                    return $item;
                }
            }
        }
        return false;
    }

    /**
     * Return list items from module storage
     * 
     * @param array $options
     * @return array
     */
    public static function getListFromModuleStorage($options = [])
    {
        $classPath = explode('\\', get_called_class());
        if (!empty(static::$storage['options']['share'])) {
            $moduleConfig = Config::share($classPath[0]);
        } else {
            $moduleConfig = Config::module($classPath[0], strpos(static::$storage['type'], 'system') !== false);
        }
        if (!empty($moduleConfig['storage']['appTypeSplit'])) {
            if (empty($options['appType'])) {
                $appType = App::$cur->type;
            } else {
                $appType = $options['appType'];
            }
            $storage = !empty($moduleConfig['storage'][$appType]) ? $moduleConfig['storage'][$appType] : [];
        } else {
            $storage = !empty($moduleConfig['storage']) ? $moduleConfig['storage'] : [];
        }
        if (!empty($storage[$classPath[1]])) {
            $items = [];
            $class = get_called_class();
            if (isset($options['key'])) {
                $arrayKey = $options['key'];
            } else {
                $arrayKey = static::index();
            }
            foreach ($storage[$classPath[1]] as $key => $item) {
                if (!empty($options['where']) && !Model::checkWhere($item, $options['where'])) {
                    continue;
                }
                $items[$item[$arrayKey]] = new $class($item);
            }
            if (!empty($options['order'])) {
                usort($items, function($a, $b) use($options) {
                    if ($a->{$options['order'][0]} > $b->{$options['order'][0]} && $options['order'][1] = 'asc') {
                        return 1;
                    } elseif ($a->{$options['order'][0]} < $b->{$options['order'][0]} && $options['order'][1] = 'asc') {
                        return -1;
                    }
                    return 0;
                });
            }
            if (!empty($options['forSelect'])) {
                $return = [];
                foreach ($items as $key => $item) {
                    $return[$key] = $item->name();
                }
                return $return;
            }
            return $items;
        }
        return [];
    }

    /**
     * Return count of records from module storage
     * 
     * @param array $options
     * @return int
     */
    public static function getCountFromModuleStorage($options = [])
    {

        $classPath = explode('\\', get_called_class());
        $count = 0;
        if (empty($options['appType'])) {
            $appType = App::$cur->type;
        } else {
            $appType = $options['appType'];
        }
        if (!empty(static::$storage['options']['share'])) {
            $moduleConfig = Config::share($classPath[0]);
        } else {
            $moduleConfig = Config::module($classPath[0], strpos(static::$storage['type'], 'system') !== false);
        }
        if (!empty($moduleConfig['storage'][$appType][$classPath[1]])) {
            $items = $moduleConfig['storage'][$appType][$classPath[1]];
            if (empty($options['where'])) {
                return count($items);
            }
            foreach ($items as $key => $item) {
                if (!empty($options['where'])) {
                    if (Model::checkWhere($item, $options['where'])) {
                        $count++;
                    }
                } else {
                    $count++;
                }
            }
        }
        return $count;
    }

    /**
     * Check where for module storage query
     * 
     * @param array $item
     * @param array|string $where
     * @param string $value
     * @param string $operation
     * @param string $concatenation
     * @return boolean
     */
    public static function checkWhere($item = [], $where = '', $value = '', $operation = '=', $concatenation = 'AND')
    {

        if (is_array($where)) {
            if (is_array($where[0])) {
                foreach ($where as $whereItem) {
                    $result = forward_static_call_array(['Model', 'checkWhere'], array_merge([$item], $whereItem));
                    if (!$result) {
                        return false;
                    }
                }
                return true;
            } else {
                return forward_static_call_array(['Model', 'checkWhere'], array_merge([$item], $where));
            }
        }

        if (!isset($item[$where]) && !$value) {
            return true;
        }
        if (!isset($item[$where]) && $value) {
            return false;
        }
        if ($item[$where] == $value) {
            return true;
        }
        return false;
    }

    /**
     * Return count of records from data base
     * 
     * @param array $options
     * @return array|int
     */
    public static function getCount($options = [])
    {
        if (static::$storage['type'] == 'moduleConfig') {
            return static::getCountFromModuleStorage($options);
        }
        $query = App::$cur->db->newQuery();
        if (!$query) {
            return 0;
        }
        if (!empty($options['where'])) {
            static::fixPrefix($options['where'], 'first');
        }
        if (!empty($options['where']))
            $query->where($options['where']);
        if (!empty($options['join']))
            $query->join($options['join']);
        if (!empty($options['order'])) {
            $query->order($options['order']);
        }
        if (!empty($options['limit']))
            $limit = (int) $options['limit'];
        else {
            $limit = 0;
        }
        if (!empty($options['start']))
            $start = (int) $options['start'];
        else {
            $start = 0;
        }
        if ($limit || $start) {
            $query->limit($start, $limit);
        }

        foreach (static::$relJoins as $join) {
            $query->join($join[0], $join[1]);
        }
        static::$relJoins = [];
        foreach (static::$needJoin as $rel) {
            $relations = static::relations();
            if (isset($relations[$rel])) {
                $type = empty($relations[$rel]['type']) ? 'to' : $relations[$rel]['type'];
                switch ($type) {
                    case 'to':
                        $relCol = $relations[$rel]['col'];
                        static::fixPrefix($relCol);
                        $query->join($relations[$rel]['model']::table(), $relations[$rel]['model']::index() . ' = ' . $relCol);
                        break;
                    case 'one':
                        $col = $relations[$rel]['col'];
                        $relations[$rel]['model']::fixPrefix($col);
                        $query->join($relations[$rel]['model']::table(), static::index() . ' = ' . $col);
                        break;
                }
            }
        }
        static::$needJoin = [];
        $cols = 'COUNT(';

        if (!empty($options['distinct'])) {
            if (is_bool($options['distinct'])) {
                $cols .= 'DISTINCT *';
            } else {
                $cols .= "DISTINCT {$options['distinct']}";
            }
        } else {
            $cols .= '*';
        }
        $cols .=') as `count`' . (!empty($options['cols']) ? ',' . $options['cols'] : '');
        $query->cols = $cols;
        if (!empty($options['group'])) {
            $query->group($options['group']);
        }
        try {
            $result = $query->select(static::table());
        } catch (PDOException $exc) {
            if ($exc->getCode() == '42S02') {
                static::createTable();
            }
            $result = $query->select(static::table());
        }
        if (!empty($options['group'])) {
            $count = $result->getArray();
            return $count;
        } else {
            $count = $result->fetch();
            return $count['count'];
        }
    }

    /**
     * Update records in data base
     * 
     * @param array $params
     * @param array $where
     * @return boolean
     */
    public static function update($params, $where = [])
    {
        static::fixPrefix($params);

        $cols = self::cols();

        $values = [];
        foreach ($cols as $col => $param) {
            if (isset($params[$col]))
                $values[$col] = $params[$col];
        }
        if (empty($values)) {
            return false;
        }

        if (!empty($where)) {
            static::fixPrefix($where, 'key');

            App::$cur->db->where($where);
        }
        App::$cur->db->update(static::table(), $values);
    }

    /**
     * Return primary key of object
     * 
     * @return mixed
     */
    public function pk()
    {
        return $this->{$this->index()};
    }

    /**
     * Before save trigger
     */
    public function beforeSave()
    {
        
    }

    /**
     * Save object to module storage
     * 
     * @param array $options
     * @return boolean
     */
    public function saveModuleStorage($options)
    {

        $col = static::index();
        $id = $this->pk();
        $appType = '';
        $classPath = explode('\\', get_called_class());

        if (!empty(static::$storage['options']['share'])) {
            $moduleConfig = Config::share($classPath[0]);
        } else {
            $moduleConfig = Config::module($classPath[0], strpos(static::$storage['type'], 'system') !== false);
        }

        if (!empty($moduleConfig['storage']['appTypeSplit'])) {
            if (empty($options['appType'])) {
                $appType = App::$cur->type;
            } else {
                $appType = $options['appType'];
            }
            $storage = !empty($moduleConfig['storage'][$appType]) ? $moduleConfig['storage'][$appType] : [];
        } else {
            $storage = !empty($moduleConfig['storage']) ? $moduleConfig['storage'] : [];
        }
        if (empty($storage[$classPath[1]])) {
            $storage[$classPath[1]] = [];
        }
        if ($id) {
            foreach ($storage[$classPath[1]] as $key => $item) {
                if ($item[$col] == $id) {
                    $storage[$classPath[1]][$key] = $this->_params;
                    break;
                }
            }
        } else {
            $id = !empty($storage['scheme'][$classPath[1]]['ai']) ? $storage['scheme'][$classPath[1]]['ai'] : 1;
            $this->$col = $id;
            $storage['scheme'][$classPath[1]]['ai'] = $id + 1;
            $storage[$classPath[1]][] = $this->_params;
        }
        if (!empty($moduleConfig['storage']['appTypeSplit'])) {
            $moduleConfig['storage'][$appType] = $storage;
        } else {
            $moduleConfig['storage'] = $storage;
        }
        if (empty(static::$storage['options']['share'])) {
            Config::save('module', $moduleConfig, $classPath[0]);
        } else {
            Config::save('share', $moduleConfig, $classPath[0]);
        }
        return true;
    }

    /**
     * Update tree path category
     */
    public function changeCategoryTree()
    {
        $class = get_class($this);
        $itemModel = $class::$treeCategory;
        $oldPath = $this->tree_path;
        $this->tree_path = $this->getCatalogTree($this);
        $itemsTable = \App::$cur->db->table_prefix . $itemModel::table();
        $itemTreeCol = $itemModel::colPrefix() . 'tree_path';

        $categoryTreeCol = $this->colPrefix() . 'tree_path';
        $categoryTable = \App::$cur->db->table_prefix . $this->table();
        if ($oldPath) {
            \App::$cur->db->query('UPDATE
                ' . $categoryTable . ' 
                    SET 
                        ' . $categoryTreeCol . ' = REPLACE(' . $categoryTreeCol . ', "' . $oldPath . $this->id . '/' . '", "' . $this->tree_path . $this->id . '/' . '") 
                    WHERE ' . $categoryTreeCol . ' LIKE "' . $oldPath . $this->id . '/' . '%"');

            \App::$cur->db->query('UPDATE
                ' . $itemsTable . '
                    SET 
                        ' . $itemTreeCol . ' = REPLACE(' . $itemTreeCol . ', "' . $oldPath . $this->id . '/' . '", "' . $this->tree_path . $this->id . '/' . '") 
                    WHERE ' . $itemTreeCol . ' LIKE "' . $oldPath . $this->id . '/' . '%"');
        }
        $itemModel::update([$itemTreeCol => $this->tree_path . $this->id . '/'], [$itemModel::colPrefix() . $this->index(), $this->id]);
    }

    /**
     * Return tree path
     * 
     * @param \Model $catalog
     * @return string
     */
    public function getCatalogTree($catalog)
    {
        $catalogClass = get_class($catalog);
        $catalogParent = $catalogClass::get($catalog->parent_id);
        if ($catalog && $catalogParent) {
            if ($catalogParent->tree_path) {
                return $catalogParent->tree_path . $catalogParent->id . '/';
            } else {
                return $this->getCatalogTree($catalogParent) . $catalogParent->id . '/';
            }
        }
        return '/';
    }

    /**
     * Update tree path item
     */
    public function changeItemTree()
    {
        $class = get_class($this);
        $categoryModel = $class::$categoryModel;
        $category = $categoryModel::get($this->{$categoryModel::index()});
        if ($category) {
            $this->tree_path = $category->tree_path . $category->pk() . '/';
        } else {
            $this->tree_path = '/';
        }
    }

    /**
     * Save object to data base
     * 
     * @param array $options
     * @return boolean|int
     */
    public function save($options = [])
    {

        if (static::$storage['type'] == 'moduleConfig') {
            return static::saveModuleStorage($options);
        }
        $class = get_class($this);
        if ($class::$categoryModel) {
            $this->changeItemTree();
        }
        if ($class::$treeCategory) {
            $this->changeCategoryTree();
        }
        if (!empty($this->_changedParams) && $this->pk()) {
            Inji::$inst->event('modelItemParamsChanged-' . get_called_class(), $this);
        }
        $this->beforeSave();

        $values = [];

        foreach ($this->cols() as $col => $param) {
            if (isset($this->_params[$col]))
                $values[$col] = $this->_params[$col];
        }
        if (empty($values) && empty($options['empty'])) {
            return false;
        }

        if ($this->pk()) {
            $new = false;
            if ($this->get($this->_params[$this->index()])) {
                App::$cur->db->where($this->index(), $this->_params[$this->index()]);
                App::$cur->db->update($this->table(), $values);
            } else {

                $this->_params[$this->index()] = App::$cur->db->insert($this->table(), $values);
            }
        } else {
            $new = true;
            $this->_params[$this->index()] = App::$cur->db->insert($this->table(), $values);
        }
        App::$cur->db->where($this->index(), $this->_params[$this->index()]);
        try {
            $result = App::$cur->db->select($this->table());
        } catch (PDOException $exc) {
            if ($exc->getCode() == '42S02') {
                $this->createTable();
            }
            $result = App::$cur->db->select($this->table());
        }
        $this->_params = $result->fetch();
        if ($new) {
            Inji::$inst->event('modelCreatedItem-' . get_called_class(), $this);
        }
        $this->afterSave();
        return $this->{$this->index()};
    }

    /**
     * After save trigger
     */
    public function afterSave()
    {
        
    }

    /**
     * Before delete trigger
     */
    public function beforeDelete()
    {
        
    }

    /**
     * Delete item from module storage
     * 
     * @param array $options
     * @return boolean
     */
    public function deleteFromModuleStorage($options)
    {

        $col = static::index();
        $id = $this->pk();
        $appType = '';
        $classPath = explode('\\', get_called_class());
        if (!empty(static::$storage['options']['share'])) {
            $moduleConfig = Config::share($classPath[0]);
        } else {
            $moduleConfig = Config::module($classPath[0], strpos(static::$storage['type'], 'system') !== false);
        }

        if (!empty($moduleConfig['storage']['appTypeSplit'])) {
            if (empty($options['appType'])) {
                $appType = App::$cur->type;
            } else {
                $appType = $options['appType'];
            }
            $storage = !empty($moduleConfig['storage'][$appType]) ? $moduleConfig['storage'][$appType] : [];
        } else {
            $storage = !empty($moduleConfig['storage']) ? $moduleConfig['storage'] : [];
        }
        if (empty($storage[$classPath[1]])) {
            $storage[$classPath[1]] = [];
        }
        foreach ($storage[$classPath[1]] as $key => $item) {

            if ($item[$col] == $id) {
                unset($storage[$classPath[1]][$key]);
                break;
            }
        }
        if (!empty($moduleConfig['storage']['appTypeSplit'])) {
            $moduleConfig['storage'][$appType] = $storage;
        } else {
            $moduleConfig['storage'] = $storage;
        }
        if (empty(static::$storage['options']['share'])) {
            Config::save('module', $moduleConfig, $classPath[0]);
        } else {
            Config::save('share', $moduleConfig, $classPath[0]);
        }
        return true;
    }

    /**
     * Delete item from data base
     * 
     * @param array $options
     * @return boolean
     */
    public function delete($options = [])
    {
        $this->beforeDelete();

        if (static::$storage['type'] == 'moduleConfig') {
            return static::deleteFromModuleStorage($options);
        }
        if (!empty($this->_params[$this->index()])) {
            App::$cur->db->where($this->index(), $this->_params[$this->index()]);
            $result = App::$cur->db->delete($this->table());
            if ($result) {
                $this->afterDelete();
                return $result;
            }
        }
        return false;
    }

    /**
     * Delete items from data base
     * 
     * @param array $where
     */
    public static function deleteList($where)
    {
        if (!empty($where)) {
            static::fixPrefix($where, 'first');
            App::$cur->db->where($where);
        }
        App::$cur->db->delete(static::table());
    }

    /**
     * After delete trigger
     */
    public function afterDelete()
    {
        
    }

    /**
     * find relation for col name
     * 
     * @param string $col
     * @return array|null
     */
    public static function findRelation($col)
    {

        foreach (static::relations() as $relName => $rel) {
            if ($rel['col'] == $col)
                return $relName;
        }
        return NULL;
    }

    /**
     * Set params for model
     * 
     * @param array $params
     */
    public function setParams($params)
    {
        static::fixPrefix($params);
        $className = get_called_class();
        foreach ($params as $paramName => $value) {
            $shortName = preg_replace('!' . $this->colPrefix() . '!', '', $paramName);
            if (!empty($className::$cols[$shortName])) {
                switch ($className::$cols[$shortName]['type']) {
                    case 'decimal':
                        $params[$paramName] = (float) $value;
                        break;
                    case 'number':
                        $params[$paramName] = (int) $value;
                        break;
                    case 'bool':
                        $params[$paramName] = (bool) $value;
                        break;
                }
            }
        }
        $this->_params = array_merge($this->_params, $params);
    }

    /**
     * Return relation
     * 
     * @param string $relName
     * @return array|boolean
     */
    public static function getRelation($relName)
    {
        $relations = static::relations();
        return !empty($relations[$relName]) ? $relations[$relName] : false;
    }

    /**
     * Load relation
     * 
     * @param string $name
     * @param array $params
     * @return null|array|integer|\Model
     */
    public function loadRelation($name, $params = [])
    {
        $relation = static::getRelation($name);
        if ($relation) {
            if (!isset($relation['type'])) {
                $type = 'to';
            } else {
                $type = $relation['type'];
            }
            $getCol = null;
            $getParams = [];
            switch ($type) {
                case 'relModel':
                    if (!$this->pk()) {
                        return [];
                    }
                    $fixedCol = $relation['model']::index();
                    $relation['relModel']::fixPrefix($fixedCol);
                    $ids = array_keys($relation['relModel']::getList(['where' => [$this->index(), $this->pk()], 'array' => true, 'key' => $fixedCol]));
                    if (empty($ids)) {
                        if (empty($params['count'])) {
                            return [];
                        } else {
                            return 0;
                        }
                    }
                    $getType = 'getList';
                    $options = [
                        'where' => [$relation['model']::index(), implode(',', $ids), 'IN'],
                        'array' => (!empty($params['array'])) ? true : false,
                        'key' => (isset($params['key'])) ? $params['key'] : ((isset($relation['resultKey'])) ? $relation['resultKey'] : null),
                        'start' => (isset($params['start'])) ? $params['start'] : ((isset($relation['start'])) ? $relation['start'] : null),
                        'order' => (isset($params['order'])) ? $params['order'] : ((isset($relation['order'])) ? $relation['order'] : null),
                        'limit' => (isset($params['limit'])) ? $params['limit'] : ((isset($relation['limit'])) ? $relation['limit'] : null),
                    ];
                    break;
                case 'many':
                    if (!$this->{$this->index()}) {
                        return [];
                    }
                    $getType = 'getList';
                    $options = [
                        'join' => (isset($relation['join'])) ? $relation['join'] : null,
                        'key' => (isset($params['key'])) ? $params['key'] : ((isset($relation['resultKey'])) ? $relation['resultKey'] : null),
                        'array' => (!empty($params['array'])) ? true : false,
                        'forSelect' => (!empty($params['forSelect'])) ? true : false,
                        'order' => (isset($params['order'])) ? $params['order'] : ((isset($relation['order'])) ? $relation['order'] : null),
                        'start' => (isset($params['start'])) ? $params['start'] : ((isset($relation['start'])) ? $relation['start'] : null),
                        'limit' => (isset($params['limit'])) ? $params['limit'] : ((isset($relation['limit'])) ? $relation['limit'] : null),
                        'appType' => (isset($params['appType'])) ? $params['appType'] : ((isset($relation['appType'])) ? $relation['appType'] : null),
                        'where' => []
                    ];
                    $options['where'][] = [$relation['col'], $this->{$this->index()}];
                    if (!empty($relation['where'])) {
                        $options['where'] = array_merge($options['where'], [$relation['where']]);
                    }
                    if (!empty($params['where'])) {
                        $options['where'] = array_merge($options['where'], [$params['where']]);
                    }
                    break;
                case 'one':
                    $getType = 'get';
                    $options = [$relation['col'], $this->pk()];
                    break;
                default:
                    if ($this->$relation['col'] === NULL) {
                        return null;
                    }
                    $getType = 'get';
                    $options = $this->$relation['col'];
                    $getParams['appType'] = $this->appType;
            }
            if (!empty($params['count'])) {
                if (class_exists($relation['model'])) {
                    return $relation['model']::getCount($options);
                }
                return 0;
            } else {
                if (class_exists($relation['model'])) {
                    $this->loadedRelations[$name][json_encode($params)] = $relation['model']::$getType($options, $getCol, $getParams);
                } else {
                    $this->loadedRelations[$name][json_encode($params)] = [];
                }
            }
            return $this->loadedRelations[$name][json_encode($params)];
        }
        return NULL;
    }

    /**
     * Add relation item
     * 
     * @param string $relName
     * @param \Model $objectId
     * @return \Model|boolean
     */
    public function addRelation($relName, $objectId)
    {
        $relation = $this->getRelation($relName);
        if ($relation) {
            $rel = $relation['relModel']::get([[$relation['model']::index(), $objectId], [$this->index(), $this->pk()]]);
            if (!$rel) {
                $rel = new $relation['relModel']([
                    $relation['model']::index() => $objectId,
                    $this->index() => $this->pk()
                ]);
                $rel->save();
            }
            return $rel;
        }
        return false;
    }

    /**
     * Check user access for form
     * 
     * @param string $formName
     * @return boolean
     */
    public function checkFormAccess($formName)
    {
        if ($formName == 'manage' && !Users\User::$cur->isAdmin()) {
            return false;
        }
        return true;
    }

    /**
     * Check access for model
     * 
     * @param string $mode
     * @param \Users\User $user
     * @return boolean
     */
    public function checkAccess($mode = 'write', $user = null)
    {
        if (!$user) {
            $user = \Users\User::$cur;
        }
        return $user->isAdmin();
    }

    /**
     * Param and relation with params getter
     * 
     * @param string $name
     * @param array $params
     * @return \Value|mixed
     */
    public function __call($name, $params)
    {
        $fixedName = $name;
        static::fixPrefix($fixedName);
        if (isset($this->_params[$fixedName])) {
            return new Value($this, $fixedName);
        } elseif (isset($this->_params[$name])) {
            return new Value($this, $name);
        }
        return call_user_func_array([$this, 'loadRelation'], array_merge([$name], $params));
    }

    /**
     * Param and relation getter
     * 
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        $fixedName = $name;
        static::fixPrefix($fixedName);
        if (isset($this->_params[$fixedName])) {
            return $this->_params[$fixedName];
        }
        if (isset($this->loadedRelations[$name][json_encode([])])) {
            return $this->loadedRelations[$name][json_encode([])];
        }
        return $this->loadRelation($name);
    }

    /**
     * Return model value in object
     * 
     * @param string $name
     * @return \Value|null
     */
    public function value($name)
    {
        $fixedName = $name;
        static::fixPrefix($fixedName);
        if (isset($this->_params[$fixedName])) {
            return new Value($this, $fixedName);
        } elseif ($this->_params[$name]) {
            return new Value($this, $name);
        }
        return null;
    }

    /**
     * Return manager filters
     * 
     * @return array
     */
    public static function managerFilters()
    {
        return [];
    }

    /**
     * Return validators for cols
     * 
     * @return array
     */
    public static function validators()
    {
        return [];
    }

    /**
     * Return validator by name
     * 
     * @param string $name
     * @return array
     */
    public static function validator($name)
    {
        $validators = static::validators();
        if (!empty($validators[$name])) {
            return $validators[$name];
        }
        return [];
    }

    /**
     * Set handler for model params
     * 
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        static::fixPrefix($name);
        $className = get_called_class();
        $shortName = preg_replace('!' . $this->colPrefix() . '!', '', $name);
        if (!empty($className::$cols[$shortName])) {
            switch ($className::$cols[$shortName]['type']) {
                case 'decimal':
                    $value = (float) $value;
                    break;
                case 'number':
                    $value = (int) $value;
                    break;
                case 'bool':
                    $value = (bool) $value;
                    break;
            }
        }
        if ((isset($this->_params[$name]) && $this->_params[$name] != $value) && !isset($this->_changedParams[$name])) {
            $this->_changedParams[$name] = $this->_params[$name];
        }

        $this->_params[$name] = $value;
    }

    /**
     * Isset handler for model params
     * 
     * @param string $name
     * @return boolean
     */
    public function __isset($name)
    {
        static::fixPrefix($name);
        return isset($this->_params[$name]);
    }

    /**
     * Convert object to string
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->name();
    }

}
