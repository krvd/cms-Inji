<?php

class MaterialsController extends adminController {

    function get_list_ajaxAction() {
        echo json_encode(Material::get_list(array('array' => true)));
    }

}

?>
