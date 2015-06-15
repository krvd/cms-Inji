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
            Msg::add('Не найдено страницы для отображения', 'danger');
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
        $category = Materials\Catalog::get($chpu, 'chpu');
        if (!$category) {
            Msg::add('Не найдено страницы для отображения', 'danger');
        }
        $this->view->setTitle($category->name);

        $pages = new Ui\Pages($_GET, ['count' => Materials\Material::getCount(['where' => ['material_catalog_id', $category->id]]), 'limit' => 20]);
        $materials = Materials\Material::get_list(['where' => ['material_catalog_id', $category->id], 'order' => ['material_date_create', 'desc'], 'start' => $pages->params['start'], 'limit' => $pages->params['limit']]);

        App::$cur->view->page(['content' => 'category', 'data' => compact('materials', 'pages', 'category')]);
    }

}

?>
