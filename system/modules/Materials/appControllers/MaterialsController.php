<?php

class MaterialsController extends Controller
{
    function indexAction()
    {
        $args = func_get_args();
        $category = null;
        $material = null;
        $alias = trim(implode('/', $args));
        if ($alias) {
            $material = Materials\Material::get($alias, 'alias');
            if (!$material) {
                $material = Materials\Material::get((int) $alias);
                if (!$material) {
                    $category = Materials\Category::get($alias, 'alias');
                    if ($category) {
                        $this->categoryAction($category->id);
                    }
                }
            }
        } else {
            $material = Materials\Material::get(1, 'default');
        }
        if (!$category && $material) {
            $this->viewAction($material->id);
        } elseif (!$category && !$material) {
            Tools::header('404');
            $this->view->page([
                'content' => '404',
                'data' => ['text' => 'Такой страницы не найдено']
            ]);
        }
    }

    function categoryAction($category_id = 0)
    {
        $category = Materials\Category::get((int) $category_id);
        if (!$category) {
            Tools::header('404');
            $this->view->page([
                'content' => '404',
                'data' => ['text' => 'Такой страницы не найдено']
            ]);
        } else {
            $this->view->setTitle($category->name);

            $pages = new Ui\Pages($_GET, ['count' => Materials\Material::getCount(['where' => ['category_id', $category->id]]), 'limit' => 10]);
            $materials = Materials\Material::getList(['where' => ['category_id', $category->id], 'order' => ['date_create', 'desc'], 'start' => $pages->params['start'], 'limit' => $pages->params['limit']]);

            $this->view->page(['page' => $category->resolveTemplate(), 'content' => $category->resolveViewer(), 'data' => compact('materials', 'pages', 'category')]);
        }
    }

    function viewAction($material_id = 0)
    {
        $material = \Materials\Material::get((int) $material_id);
        if (!$material) {
            Tools::header('404');
            $this->view->page([
                'content' => '404',
                'data' => ['text' => 'Такой страницы не найдено']
            ]);
        } else {
            if ($material->keywords) {
                $this->view->addMetaTag(['name' => 'keywords', 'content' => $material->keywords]);
            }
            if ($material->description) {
                $this->view->addMetaTag(['name' => 'description', 'content' => $material->description]);
            }
            $this->view->addMetaTag(['property' => 'og:title', 'content' => $material->name . ' ' . $material->keywords]);
            $this->view->addMetaTag(['property' => 'og:url', 'content' => 'http://' . idn_to_utf8(INJI_DOMAIN_NAME) . '/' . $material->alias]);
            if ($material->description) {
                $this->view->addMetaTag(['property' => 'og:description', 'content' => 'http://' . idn_to_utf8(INJI_DOMAIN_NAME) . '/' . $material->description]);
            }
            if ($material->image) {
                $this->view->addMetaTag(['property' => 'og:image', 'content' => 'http://' . idn_to_utf8(INJI_DOMAIN_NAME) . $material->image->path]);
            } elseif ($logo = Files\File::get('site_logo', 'code')) {
                $this->view->addMetaTag(['property' => 'og:image', 'content' => 'http://' . idn_to_utf8(INJI_DOMAIN_NAME) . $logo->path]);
            }
            $this->view->setTitle($material->name . ' ' . $material->keywords);
            $bread[] = ['text' => $material->name, 'href' => '/' . $material->alias];
            $this->view->page([
                'page' => $material->resolveTemplate(),
                'content' => $material->resolveViewer(),
                'data' => compact('material', 'bread'),
            ]);
        }
    }

}
