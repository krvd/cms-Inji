<?php

class Model {

    static $storage = ['type' => 'db'];
    static $objectName = '';
    public $_params = [];
    public $loadedRelations = [];
    static $treeCategory = '';
    static $categoryModel = '';
    static $labels = [];
    static $forms = [];
    static $cols = [];
    static $view = [];
    static $needJoin = [];
    static $relJoins = [];

    function __construct($params = array()) {
        $this->setParams($params);
    }

    static function getColValue($object, $valuePath) {
        if (strpos($valuePath, ':')) {
            $rel = substr($valuePath, 0, strpos($valuePath, ':'));
            $param = substr($valuePath, strpos($valuePath, ':') + 1);
            if (strpos($valuePath, ':')) {
                return self::getColValue($object->$rel, $param);
            } else {
                return $object->$rel->$param;
            }
        } else {
            return $object->$valuePath;
        }
    }

    static function fixPrefix(&$array, $searchtype = 'key', $rootModel = '') {
        if (!$rootModel) {
            $rootModel = get_called_class();
        }
        $cols = static::cols();
        if (!$array) {
            return;
        }
        if (!is_array($array)) {
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

    static function checkForJoin(&$col, $rootModel) {

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
                        $relCol = $relations[$rel]['col'];
                        $relations[$rel]['model']::fixPrefix($relCol);
                        $rootModel::$relJoins[$relations[$rel]['model'] . '_' . $rel] = [$relations[$rel]['model']::table(), static::index() . ' = ' . $relCol];
                        break;
                }
                $relations[$rel]['model']::fixPrefix($col, 'key', $rootModel);
            }
        }
    }

    static function getColInfo($col) {
        return static::parseColRecursion($col);
    }

    private static function parseColRecursion($info) {
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

    static function cols() {
        if (empty(Model::$cols[static::table()])) {
            Model::$cols[static::table()] = App::$cur->db->getTableCols(static::table());
        }
        return Model::$cols[static::table()];
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
        return 'name';
    }

    function name() {
        return $this->name ? $this->name : $this->pk();
    }

    static function get($param = null, $col = null, $options = []) {
        if (static::$storage['type'] == 'moduleConfig') {
            return static::getFromModuleStorage($param, $col, $options);
        }
        if ($col) {
            static::fixPrefix($col);
        }

        if (is_array($param)) {
            static::fixPrefix($param, 'first');
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
        if (!empty($options['group'])) {
            App::$cur->db->group($options['group']);
        }
        if (!empty($options['order']))
            App::$cur->db->order($options['order']);
        if (!empty($options['join']))
            App::$cur->db->join($options['join']);

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
     * New method
     * 
     * @param type $options
     * @return type
     */
    static function getList($options = []) {
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
        if (!empty($options['where'])) {
            static::fixPrefix($options['where'], 'first');
        }
        $return = array();
        if (!empty($options['where']))
            App::$cur->db->where($options['where']);
        if (!empty($options['join']))
            App::$cur->db->join($options['join']);
        if (!empty($options['order'])) {
            App::$cur->db->order($options['order']);
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
            App::$cur->db->limit($start, $limit);
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
        if (!empty($options['group'])) {
            App::$cur->db->group($options['group']);
            App::$cur->db->cols = 'COUNT(*) as `count`' . (!empty($options['cols']) ? ',' . $options['cols'] : '');
            $count = App::$cur->db->select(static::table())->getArray();
            return $count;
        } else {
            App::$cur->db->cols = 'COUNT(*) as `count`';
            $count = App::$cur->db->select(static::table())->fetch();
            return $count['count'];
        }
    }

    static function update($params, $where = []) {

        $cols = self::cols();

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

    function changeCategoryTree() {
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
        $array = [$itemTreeCol => $this->tree_path . $this->id . '/'];
        $itemModel::update([$itemTreeCol => $this->tree_path . $this->id . '/'], [$itemModel::colPrefix() . $this->index(), $this->id]);
    }

    function getCatalogTree($catalog) {
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

    function changeItemTree() {
        $class = get_class($this);
        $categoryModel = $class::$categoryModel;
        $category = $categoryModel::get($this->{$categoryModel::index()});
        if ($category) {
            $this->tree_path = $category->tree_path . $category->pk() . '/';
        } else {
            $this->tree_path = '/';
        }
    }

    function save($options = []) {

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

    function delete($options = []) {
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
        static::fixPrefix($params);
        $this->_params = array_merge($this->_params, $params);
    }

    static function getRelationOptions($relName) {
        $relations = static::relations();
        return $relations[$relName];
    }

    function loadRelation($name, $params = []) {
        $relations = $this->relations();
        if (isset($relations[$name])) {
            $relation = $relations[$name];
            if (!isset($relation['type']))
                $type = 'to';
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
                        'join' => (isset($relation['params']['join'])) ? $relation['params']['join'] : null,
                        'order' => (isset($relation['params']['order'])) ? $relation['params']['join'] : null,
                        'key' => (isset($params['key'])) ? $params['key'] : ((isset($relation['resultKey'])) ? $relation['resultKey'] : null),
                        'array' => (!empty($params['array'])) ? true : false,
                        'order' => (isset($params['order'])) ? $params['order'] : ((isset($relation['order'])) ? $relation['order'] : null),
                        'start' => (isset($params['start'])) ? $params['start'] : ((isset($relation['start'])) ? $relation['start'] : null),
                        'limit' => (isset($params['limit'])) ? $params['limit'] : ((isset($relation['limit'])) ? $relation['limit'] : null),
                        'appType' => (isset($params['appType'])) ? $params['appType'] : ((isset($relation['appType'])) ? $relation['appType'] : null),
                    ];
                    $options['where'] = [$relation['col'], $this->{$this->index()}];
                    if (!empty($params['where'])) {
                        $options['where'] = array_merge([$options['where']], [$params['where']]);
                    }
                    break;
                case 'one':
                    $getType = 'get';
                    $options = [$relation['col'], $this->pk()];
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
            App::$cur->db->where($relation['relTablePrefix'] . $this->index(), $this->pk());
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
        if ($formName == 'manage' && !Users\User::$cur->isAdmin()) {
            return false;
        }
        return true;
    }

    function checkAccess($mode = 'write', $user = null) {
        if (!$user) {
            $user = \Users\User::$cur;
        }
        return $user->isAdmin();
    }

    function __call($name, $params) {
        $fixedName = $name;
        static::fixPrefix($fixedName);
        if (isset($this->_params[$fixedName])) {
            return new Value($this, $fixedName);
        } elseif (isset($this->_params[$name])) {
            return new Value($this, $name);
        }
        return call_user_func_array([$this, 'loadRelation'], array_merge([$name], $params));
    }

    function __get($name) {
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

    function value($name) {
        $fixedName = $name;
        static::fixPrefix($fixedName);
        if (isset($this->_params[$fixedName])) {
            return new Value($this, $fixedName);
        } elseif ($this->_params[$name]) {
            return new Value($this, $name);
        }
        return null;
    }

    function __set($name, $value) {
        static::fixPrefix($name);
        $this->_params[$name] = $value;
    }

    function __isset($name) {
        static::fixPrefix($name);
        return isset($this->_params[$name]);
    }

    public function __toString() {
        if (!empty($this->_params['name'])) {
            return $this->_params['name'];
        }
        return $this->pk();
    }

}
