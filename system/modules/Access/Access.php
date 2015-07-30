<?php

class Access extends Module {

    function getDeniedRedirect($app = false) {
        if (!$app) {
            $app = $this->app->type;
        }
        if (!empty($this->config['access']['accessTree'][$app]['deniedUrl']))
            return $this->config['access']['accessTree'][$app]['deniedUrl'];

        return '/';
    }

    function checkAccess($element, $user = null) {
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
                $access = $this->resovePath($accesses, $path, '_access');
            }
            if (is_null($access) && isset($this->config['access'])) {
                $accesses = $this->config['access'];
                $access = $this->resovePath($accesses, $path, '_access');
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
        

        if ((!$user->group_id && !empty($access)) || ($user->group_id && !empty($access) && !in_array($user->user_group_id, $access)))
            return false;

        return true;
    }

    function resovePath($array, $path, $element) {
        while ($path) {
            $result = $this->pathWalker($array, array_merge($path, [$element]));
            if ($result !== null) {
                return $result;
            }
            $path = array_slice($path, 0, -1);
        }
        return null;
    }

    function pathWalker($array, $path) {
        if ($path && isset($array[$path[0]])) {
            return $this->pathWalker($array[$path[0]], array_slice($path, 1));
        } elseif (!$path) {
            return $array;
        } else {
            return NULL;
        }
    }

}
