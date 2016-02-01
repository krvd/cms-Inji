<?php

/**
 * Access module
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class Access extends Module
{
    public function getDeniedRedirect($app = false)
    {
        if (!$app) {
            $app = $this->app->type;
        }
        if (!empty($this->config['access']['accessTree'][$app]['deniedUrl']))
            return $this->config['access']['accessTree'][$app]['deniedUrl'];

        return '/';
    }

    public function checkAccess($element, $user = null)
    {
        $access = NULL;
        if ($element instanceof Controller) {
            $path = [
                'accessTree',
                $element->module->app->type,
                $element->name,
                $element->method
            ];
            if (isset($element->module->config['access'])) {
                $accesses = $element->module->config['access'];
                $access = $this->resolvePath($accesses, $path, '_access');
            }
            if (is_null($access) && isset($this->config['access'])) {
                $accesses = $this->config['access'];
                $access = $this->resolvePath($accesses, $path, '_access');
            }
        }
        if (is_null($access)) {
            $access = [];
        }
        if (is_null($user)) {
            $user = Users\User::$cur;
        }
        if (empty($access)) {
            return true;
        }

        if ((!$user->group_id && !empty($access)) || ($user->group_id && !empty($access) && !in_array($user->group_id, $access)))
            return false;

        return true;
    }

    public function resolvePath($array, $path, $element)
    {
        while ($path) {
            $result = $this->pathWalker($array, array_merge($path, [$element]));
            if ($result !== null) {
                return $result;
            }
            $path = array_slice($path, 0, -1);
        }
        return null;
    }

    public function pathWalker($array, $path)
    {
        if ($path && isset($array[$path[0]])) {
            return $this->pathWalker($array[$path[0]], array_slice($path, 1));
        } elseif (!$path) {
            return $array;
        } else {
            return NULL;
        }
    }

}
