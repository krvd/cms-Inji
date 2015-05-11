<?php

class materialsController extends Controller
{

    function indexAction()
    {
        $args = func_get_args();
        $category = null;
        $material = null;

        $material_chpu = trim(implode('/', $args));
        if ($material_chpu) {
            $material = Material::get($material_chpu, 'material_chpu');
            if (!$material) {
                $category = MaterialCatalog::get($material_chpu, 'mc_chpu');
                if ($category) {
                    $this->categoryAction($material_chpu);
                } else {
                    $material = Material::get(1, 'material_default');
                }
            }
        } else {
            $material = Material::get(1, 'material_default');
        }

        if (!$material && !$category) {
            Inji::app()->msg->add('Не найдено страницы для отображения', 'danger');
        }
        if (!$category) {
            $this->view->set_title($material->material_name);
            Inji::app()->view->page($material->material_template, $material->material_viewer, compact('material', 'title', 'bread'));
        }
    }

    function categoryAction()
    {
        $args = func_get_args();
        $category = null;
        $chpu = trim(implode('/', $args));
        $category = MaterialCatalog::get($chpu, 'mc_chpu');
        if (!$category) {
            Inji::app()->msg->add('Не найдено страницы для отображения', 'danger');
        }
        $this->view->set_title($category->mc_name);

        $pages = new Pages($_GET, ['count' => Material::getCount(['where' => ['material_mc_id', $category->mc_id]]), 'limit' => 20]);
        $materials = Material::get_list(['where' => ['material_mc_id', $category->mc_id], 'order' => ['material_date_create', 'desc'], 'start' => $pages->params['start'], 'limit' => $pages->params['limit']]);

        Inji::app()->view->page('category', compact('materials', 'pages', 'category'));
    }

}

?>
