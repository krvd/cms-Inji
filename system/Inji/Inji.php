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

    static $inst = NULL;
    private static $_listeners = [];
    public static $config = [];

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

}
