<?php

class MaterialsController extends Controller {

    function indexAction() {
        $args = func_get_args();
        $category = null;
        $material = null;

        $alias = trim(implode('/', $args));
        if ($alias) {
            $material = Materials\Material::get($alias, 'alias');
            if (!$material) {
                $category = Materials\Category::get($alias, 'alias');
                if ($category) {
                    $this->categoryAction($alias);
                }
            }
        } else {
            $material = Materials\Material::get(1, 'default');
        }



        if (!$category && $material) {
            if ($material->keywords) {
                $this->view->addMetaTag(['name' => 'keywords', 'content' => $material->keywords]);
            }
            if ($material->description) {
                $this->view->addMetaTag(['name' => 'description', 'content' => $material->description]);
            }
            $this->view->addMetaTag(['property' => 'og:title', 'content' => $material->name . ' ' . $material->keywords]);
            $this->view->addMetaTag(['property' => 'og:url', 'content' => 'http://' . INJI_DOMAIN_NAME . '/' . $material->alias]);
            if ($material->description) {
                $this->view->addMetaTag(['property' => 'og:description', 'content' => 'http://' . INJI_DOMAIN_NAME . '/' . $material->description]);
            }
            if ($material->image) {
                $this->view->addMetaTag(['property' => 'og:image', 'content' => 'http://' . INJI_DOMAIN_NAME . $material->image->path]);
            } elseif ($logo = Files\File::get('site_logo', 'code')) {
                $this->view->addMetaTag(['property' => 'og:image', 'content' => 'http://' . INJI_DOMAIN_NAME . $logo->path]);
            }
            $this->view->setTitle($material->name . ' ' . $material->keywords);
            $this->view->page([
                'page' => $material->template,
                'content' => $material->viewer,
                'data' => compact('material')
            ]);
        } elseif (!$category && !$material) {
            Tools::header('404');
            $this->view->page([
                'content' => '404',
                'data' => ['text' => 'Такой страницы не найдено']
            ]);
        }
    }

    function categoryAction() {
        $args = func_get_args();
        $category = null;
        $chpu = trim(implode('/', $args));
        $category = Materials\Category::get($chpu, 'alias');
        if (!$category) {
            Msg::add('Не найдено страницы для отображения', 'danger');
        }
        $this->view->setTitle($category->name);

        $pages = new Ui\Pages($_GET, ['count' => Materials\Material::getCount(['where' => ['category_id', $category->id]]), 'limit' => 20]);
        $materials = Materials\Material::get_list(['where' => ['category_id', $category->id], 'order' => ['date_create', 'desc'], 'start' => $pages->params['start'], 'limit' => $pages->params['limit']]);

        App::$cur->view->page(['content' => 'category', 'data' => compact('materials', 'pages', 'category')]);
    }

}

?>
