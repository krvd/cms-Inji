<?php

class Model {

    static $storage = ['type' => 'db'];
    static $objectName = '';
    public $modelLogKey = 0;
    public $_params = [];
    public $loadedRelations = [];
    static $labels = [];
    static $forms = [];
    static protected $cols = [];

    function __construct($params = array()) {
        $this->setParams($params);
    }

    static function cols() {
        if (empty(static::$cols[static::table()])) {
            static::$cols[static::table()] = App::$cur->db->getTableCols(static::table());
        }
        else {
            static::$cols[static::table()] = [];
        }
        return static::$cols[static::table()];
    }

    static function table() {
        return strtolower(str_replace('\\', '_', get_called_class()));
    }

    static function index() {

        return static::colPrefix() . 'id';
    }

    static function colPrefix() {
        $classPath = explode('\\', get_called_class());
        return strtolower($classPath[1]) . '_';
    }

    static function relations() {
        return [];
    }

    static function nameCol() {
        return null;
    }

    static function get($param = null, $col = null, $options = []) {
        if (static::$storage['type'] == 'moduleConfig') {
            return static::getFromModuleStorage($param, $col, $options);
        }
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
        $result = App::$cur->db->select(static::table());
        if (!$result) {
            return false;
        }
        return $result->fetch(get_called_class());
    }

    /**
     * Old method
     * 
     * @param type $options
     * @return \class
     */
    static function get_list($options = []) {

        $return = array();
        if (!empty($options['where']))
            App::$cur->db->where($options['where']);
        if (!empty($options['order']))
            App::$cur->db->order($options['order']);
        if (!empty($options['join']))
            App::$cur->db->join($options['join']);
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
            App::$cur->db->limit($start, $limit);
        }
        if (isset($options['key'])) {
            $key = $options['key'];
        } else {
            $key = static::index();
        }
        $result = App::$cur->db->select(static::table());
        $rows = App::$cur->db->result_array($result, $key);
        $class = get_called_class();
        if (empty($options['array'])) {
            foreach ($rows as $row) {
                if ($key) {
                    $return[$row[$key]] = new $class($row);
                } else {
                    $return[] = new $class($row);
                }
            }
            return $return;
        }
        return $rows;
    }

    /**
     * New method
     * 
     * @param type $options
     * @return type
     */
    static function getList($options = []) {
        if (static::$storage['type'] != 'db') {
            return static::getListFromModuleStorage($options);
        }
        return static::get_list($options);
    }

    static function getFromModuleStorage($param = null, $col = null, $options = []) {
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
            $items = $storage[$classPath[1]];
            $class = get_called_class();
            foreach ($items as $key => $item) {
                if ($item[$col] == $param) {
                    if (!empty($options['array'])) {
                        return $item;
                    }
                    return new $class($item);
                }
            }
        }
        return [];
    }

    static function getListFromModuleStorage($options = []) {
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
            foreach ($storage[$classPath[1]] as $key => $item) {
                if (!empty($options['where']) && !Model::checkWhere($item, $options['where'])) {
                    continue;
                }
                $items[$item[static::index()]] = new $class($item);
            }
            return $items;
        }
        return [];
    }

    static function getCountFromModuleStorage($options = []) {

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

    static function checkWhere($item = [], $where = '', $value = '', $operation = '=', $concatenation = 'AND') {
        if (is_array($where)) {
            return forward_static_call_array(['Model', 'checkWhere'], array_merge([$item], $where));
        }
        if ($item[$where] == $value) {
            return true;
        }
        return false;
    }

    static function getCount($options = array()) {

        if (static::$storage['type'] == 'moduleConfig') {
            return static::getCountFromModuleStorage($options);
        }
        $return = array();
        if (!empty($options['where']))
            App::$cur->db->where($options['where']);
        if (!empty($options['join']))
            App::$cur->db->join($options['join']);

        App::$cur->db->cols = 'COUNT(*) as `count`';
        $result = App::$cur->db->select(static::table());
        $count = $result->fetch_assoc();
        return $count['count'];
    }

    static function update($params, $where = []) {

        $cols = App::$cur->db->getTableCols(static::table());

        $values = array();
        foreach ($cols as $col => $param) {
            if (isset($params[$col]))
                $values[$col] = $params[$col];
        }
        if (!$values)
            return false;
        if ($where) {
            App::$cur->db->where($where);
        }
        App::$cur->db->update(static::table(), $values);
    }

    function pk() {
        return $this->{$this->index()};
    }

    function beforeSave() {
        
    }

    function saveModuleStorage($options) {

        $col = static::index();
        $id = $this->pk();

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

    function save($options = []) {

        if (static::$storage['type'] == 'moduleConfig') {
            return static::saveModuleStorage($options);
        }
        $this->beforeSave();

        $values = array();

        foreach ($this->cols() as $col => $param) {
            if (isset($this->_params[$col]))
                $values[$col] = $this->_params[$col];
        }
        if (!$values)
            return false;

        if (!empty($this->_params[$this->index()])) {
            if ($this->get($this->_params[$this->index()])) {
                App::$cur->db->where($this->index(), $this->_params[$this->index()]);
                App::$cur->db->update($this->table(), $values);
            } else {

                $this->_params[$this->index()] = App::$cur->db->insert($this->table(), $values);
            }
        } else {
            $this->_params[$this->index()] = App::$cur->db->insert($this->table(), $values);
        }
        App::$cur->db->where($this->index(), $this->_params[$this->index()]);
        $result = App::$cur->db->select($this->table());
        $this->_params = $result->fetch();
        $this->afterSave();
        return $this->{$this->index()};
    }

    function afterSave() {
        
    }

    function beforeDelete() {
        
    }

    function deleteFromModuleStorage($options) {

        $col = static::index();
        $id = $this->pk();

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

    function delete($options) {
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

    function afterDelete() {
        
    }

    static function findRelation($col) {

        foreach (static::relations() as $relName => $rel) {
            if ($rel['col'] == $col)
                return $relName;
        }
        return NULL;
    }

    function setParams($params) {
        $this->_params = array_merge($this->_params, $params);
    }

    function loadRelation($name, $params = []) {
        $relations = $this->relations();
        if (isset($relations[$name])) {
            $relation = $relations[$name];
            if (!isset($relation['type']))
                $type = 'one';
            else
                $type = $relation['type'];

            switch ($type) {
                case 'relTable':
                    if (!$this->{$this->index()}) {
                        return [];
                    }
                    App::$cur->db->where($relation['relTablePrefix'] . $this->index(), $this->{$this->index()});
                    $ids = App::$cur->db->result_array(App::$cur->db->select($relation['relTable']), $relation['relTablePrefix'] . $relation['model']::index());
                    $getType = 'get_list';
                    $options = [
                        'where' => [$relation['model']::index(), implode(',', array_keys($ids)), 'IN'],
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
                        'where' => [$relation['col'], $this->{$this->index()}],
                        'join' => (isset($relation['params']['join'])) ? $relation['params']['join'] : null,
                        'order' => (isset($relation['params']['order'])) ? $relation['params']['join'] : null,
                        'key' => (isset($params['key'])) ? $params['key'] : ((isset($relation['resultKey'])) ? $relation['resultKey'] : null),
                        'array' => (!empty($params['array'])) ? true : false,
                        'order' => (isset($params['order'])) ? $params['order'] : ((isset($relation['order'])) ? $relation['order'] : null),
                        'start' => (isset($params['start'])) ? $params['start'] : ((isset($relation['start'])) ? $relation['start'] : null),
                        'limit' => (isset($params['limit'])) ? $params['limit'] : ((isset($relation['limit'])) ? $relation['limit'] : null),
                        'appType' => (isset($params['appType'])) ? $params['appType'] : ((isset($relation['appType'])) ? $relation['appType'] : null),
                    ];
                    break;
                default:
                    if ($this->$relation['col'] === NULL)
                        return NULL;
                    $getType = 'get';
                    $options = $this->$relation['col'];
            }

            if (!empty($params['count'])) {
                return $relation['model']::getCount($options);
            } else {
                $this->loadedRelations[$name][json_encode($params)] = $relation['model']::$getType($options);
            }
            return $this->loadedRelations[$name][json_encode($params)];
        }
        return NULL;
    }

    function addRelation($relName, $objectId) {
        $relations = $this->relations();
        if (isset($relations[$relName])) {
            $relation = $relations[$relName];
            App::$cur->db->where($relation['relTablePrefix'] . $this->index(), $this->{$this->index()});
            App::$cur->db->where($relation['relTablePrefix'] . $relation['model']::index(), $objectId);
            $isset = App::$cur->db->select($relation['relTable'])->fetch_assoc();
            if ($isset)
                return true;

            App::$cur->db->insert($relation['relTable'], [
                $relation['relTablePrefix'] . $this->index() => $this->{$this->index()},
                $relation['relTablePrefix'] . $relation['model']::index() => $objectId
            ]);
            return true;
        }
        return false;
    }

    function checkFormAccess($formName) {
        if ($formName == 'manage' && !App::$cur->Users->curUser->isAdmin()) {
            return false;
        }
        return true;
    }

    function __call($name, $params) {
        return call_user_func_array([$this, 'loadRelation'], array_merge([$name], $params));
    }

    function __get($name) {
        if (isset($this->_params[$name])) {
            return $this->_params[$name];
        }
        if (isset($this->_params[static::colPrefix() . $name])) {
            return $this->_params[static::colPrefix() . $name];
        }
        if (isset($this->loadedRelations[$name][json_encode([])])) {
            return $this->loadedRelations[$name][json_encode([])];
        }
        return $this->loadRelation($name);
    }

    function __set($name, $value) {
        $this->_params[$name] = $value;
    }

    public function __toString() {
        if (!empty($this->_params['name'])) {
            return $this->_params['name'];
        }
        return $this->pk();
    }

}
