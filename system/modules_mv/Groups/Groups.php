<?php

class Groups extends Module {

    function check_method_dostup($controller, $method) {

        $dostup = array();
        if (isset($this->Config->site['module_dostup'][$controller][$method]['_dostup']))
            $dostup = $this->Config->site['module_dostup'][$controller][$method]['_dostup'];
        elseif (isset($this->Config->site['module_dostup'][$controller]['_dostup']))
            $dostup = $this->Config->site['module_dostup'][$controller]['_dostup'];
        elseif (isset($this->Config->site['module_dostup']['_dostup']))
            $dostup = $this->Config->site['module_dostup']['_dostup'];

        if (!empty($dostup) && !in_array($this->user['user_group'], $dostup))
            return false;

        return true;
    }

    function create($group_name) {
        return $this->db->insert('groups', array('group_name' => $group_name));
    }

    function get_all() {
        return $this->db->result_array($this->db->select('groups'), 'group_id');
    }

}
