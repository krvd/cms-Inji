<?php

class Access extends Module {

    function check_method($controller, $method) {
        $accesses = $this->modConf[Inji::app()->app['type']];
        $access = array();

        if (isset($accesses['dostup_tree'][$controller][$method]['_access']))
            $access = $accesses['dostup_tree'][$controller][$method]['_access'];
        elseif (isset($accesses['dostup_tree'][$controller]['_access']))
            $access = $accesses['dostup_tree'][$controller]['_access'];
        elseif (isset($accesses['dostup_tree']['_access']))
            $access = $accesses['dostup_tree']['_access'];
        if (Inji::app()->Users->cur->user_group_id && !empty($access) && !in_array(Inji::app()->Users->cur->user_group_id, $access))
            Inji::app()->url->redirect($accesses['denied_redirect'], 'У вас нет прав доступа');

        return true;
    }

}
