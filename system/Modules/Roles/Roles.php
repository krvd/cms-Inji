<?php

class Roles extends Module
{

    function get_all()
    {
        $result = $this->db->select('roles');
        if(!$result){
            return [];
        }
        return $this->db->result_array($result, 'role_id');
    }

    function create($role_name, $role_group_id)
    {
        return $this->db->insert('roles', array('role_name' => $role_name, 'role_group_id' => $role_group_id));
    }

}
