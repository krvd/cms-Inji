<?php

class MaterialsController extends Controller {

    function indexAction() {
        $args = func_get_args();
        $category = null;
        $material = null;

        $material_chpu = trim(implode('/', $args));
        if ($material_chpu) {
            $material = Materials\Material::get($material_chpu, 'chpu');
            if (!$material) {
                $category = Materials\Catalog::get($material_chpu, 'chpu');
                if ($category) {
                    $this->categoryAction($material_chpu);
                } else {
                    $material = Materials\Material::get(1, 'default');
                }
            }
        } else {
            $material = Materials\Material::get(1, 'default');
        }

        if (!$material && !$category) {
            App::$cur->msg->add('Не найдено страницы для отображения', 'danger');
        }
        if (!$category) {
            $this->view->setTitle($material->material_name);
            $this->view->page([
                'template' => $material->material_template,
                'content' => $material->material_viewer,
                'data' => compact('material', 'title', 'bread')
            ]);
        }
    }

    function categoryAction() {
        $args = func_get_args();
        $category = null;
        $chpu = trim(implode('/', $args));
        $category = MaterialCatalog::get($chpu, 'mc_chpu');
        if (!$category) {
            App::$cur->msg->add('Не найдено страницы для отображения', 'danger');
        }
        $this->view->set_title($category->mc_name);

        $pages = new Pages($_GET, ['count' => Material::getCount(['where' => ['material_mc_id', $category->mc_id]]), 'limit' => 20]);
        $materials = Material::get_list(['where' => ['material_mc_id', $category->mc_id], 'order' => ['material_date_create', 'desc'], 'start' => $pages->params['start'], 'limit' => $pages->params['limit']]);

        App::$cur->view->page('category', compact('materials', 'pages', 'category'));
    }

}

?>
