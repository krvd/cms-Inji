<?php

/**
 * Inji core file
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class Inji {

    private static $_app = NULL;
    private $_objects = [];
    private $_listeners = [];
    public $curApp = [];
    public $params = [];

    /**
     * Set app instance to static param
     * 
     * @param object $app
     */
    static function setApp($app) {
        self::$_app = $app;
    }

    /**
     * Get app instance
     * 
     * @return object
     */
    static function app() {
        return self::$_app;
    }

    /**
     * Add event listener
     * 
     * @param string $eventName
     * @param string $listenCode
     * @param array|function $callback
     */
    function listen($eventName, $listenCode, $callback) {
        $this->_listeners[$eventName][$listenCode] = $callback;
    }

    /**
     * Throw event
     * 
     * @param string $eventName
     * @param mixed $eventObject
     * @return mixed
     */
    function event($eventName, $eventObject = null) {
        $event = [
            'eventName' => $eventName,
            'eventObject' => $eventObject,
        ];
        if (!empty($this->_listeners[$eventName])) {
            $iteration = 0;
            $calledBefore = [];
            foreach ($this->_listeners[$eventName] as $listenCode => $callback) {
                $event['iteration'] = ++$iteration;
                $event['calledBefore'] = $calledBefore;
                if (is_callable($callback)) {
                    $eventObject = $callback($event);
                } elseif (is_array($callback) && isset($callback['callback'])) {
                    $eventObject = $callback($event, $callback);
                } else {
                    $eventObject = $this->{$callback['module']}->{$callback['method']}($event, $callback);
                }
                $calledBefore[$iteration] = $listenCode;
            }
        }
        return $eventObject;
    }

    /**
     * Unlisten event
     * 
     * @param string $eventName
     * @param string $listenCode
     */
    function unlisten($eventName, $listenCode) {
        if (!empty($this->_listeners[$eventName][$listenCode])) {
            unset($this->_listeners[$eventName][$listenCode]);
        }
    }

    function loadClass($className) {
        if (file_exists(INJI_SYSTEM_DIR . '/Inji/' . $className . '.php')) {
            include INJI_SYSTEM_DIR . '/Inji/' . $className . '.php';
            return true;
        }
        return false;
    }

    function __get($className) {
        $className = ucfirst($className);
        if (isset($this->_objects[$className])) {
            return $this->_objects[$className];
        }
        if ($this->loadClass($className)) {
            $this->_objects[$className] = new $className();
        } else {
            $object = $this->event('UninitializeObjectCalled', $className);
            if (is_object($object)) {
                $this->_objects[$className] = $object;
            }
        }
        if (isset($this->_objects[$className])) {
            if (method_exists($this->_objects[$className], 'init')) {
                $this->_objects[$className]->init();
            }
            $this->event('NewObjectInitialize', $className);
            return $this->_objects[$className];
        }
        return null;
    }

}
