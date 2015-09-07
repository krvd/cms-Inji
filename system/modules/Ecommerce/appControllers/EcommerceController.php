<?php

class ecommerceController extends Controller {

    function cabinetAction() {
        $this->view->setTitle('Кабинет');
        $this->view->page();
    }

    function autoCompleteAction() {
        $items = \Ecommerce\Item::getList(['cols' => ['name', 'search_index']]);
        $return = [];
        foreach ($items as $item) {
            $return[] = ['name' => $item->name(), 'search' => $item->search_index];
        }
        echo json_encode($return);
    }

    function indexAction($catalog_id = 0) {
        Tools::redirect('/ecommerce/itemList');
        $catalog = Ecommerce\Category::get((int) $catalog_id);

        if ($catalog) {
            $this->url->redirect('/ecommerce/itemList/' . (int) $catalog_id);
        }
        $this->view->page('main');
    }

    function itemListAction($category_id = 0) {
        if (!empty($_GET['search'])) {
            if (!empty($_GET['inCatalog'])) {
                $category_id = (int) $_GET['inCatalog'];
            }
            $search = $_GET['search'];
        } else
            $search = '';

        if (!empty($_GET['sort'])) {
            $sort = $_GET['sort'];
        } else {
            $sort = [];
        }

        $pages = new \Ui\Pages($_GET, ['count' => $this->ecommerce->getItemsCount([
                'parent' => $category_id,
                'search' => trim($search),
                'filters' => !empty($_GET['filters']) ? $_GET['filters'] : []
            ]),
            'limit' => 18,
        ]);

        $category_id = (int) $category_id;

        if ($category_id < 1)
            $category_id = '';

        $active = $category_id;
        $category = null;
        if ($category_id)
            $category = Ecommerce\Category::get($category_id);

        $bread = [];
        if (!$category || !$category->name) {
            $bread[] = array('text' => 'Каталог');
            $this->view->setTitle('Каталог');
        } else {
            $bread[] = array('text' => 'Каталог', 'href' => '/ecommerce');
            $categoryIds = array_values(array_filter(explode('/', $category->tree_path)));
            $categoryIds = array_reverse($categoryIds);
            foreach ($categoryIds as $id) {
                $cat = Ecommerce\Category::get($id);
                $bread[] = array('text' => $cat->name, 'href' => '/ecommerce/itemList/' . $cat->id);
            }
            $this->view->setTitle($category->name);
        }

        $items = $this->ecommerce->getItems([
            'parent' => !empty($category_ids) ? $category_ids : $category_id,
            'start' => $pages->params['start'],
            'count' => $pages->params['limit'],
            'search' => trim($search),
            'sort' => $sort,
            'filters' => !empty($_GET['filters']) ? $_GET['filters'] : []
        ]);
        $categorys = Ecommerce\Category::getList();
        $this->view->page(['data' => compact('active', 'category', 'sort', 'search', 'pages', 'items', 'categorys', 'bread')]);
    }

    function viewAction($id = '') {
        $item = \Ecommerce\Item::get((int) $id);
        if (!$item) {
            $this->url->redirect('/ecommerce/', 'Такой товар не найден');
        }
        $active = $item->category_id;
        $catalog = $item->category;

        $bread[] = array('text' => 'Каталог', 'href' => '/ecommerce');

        $catalogIds = array_values(array_filter(explode('/', $item->tree_path)));
        $catalogIds = array_reverse($catalogIds);
        foreach ($catalogIds as $id) {
            $cat = Ecommerce\Category::get($id);
            $bread[] = array('text' => $cat->name, 'href' => '/ecommerce/itemList/' . $cat->id);
        }
        $bread[] = array('text' => $item->name());
        $this->view->setTitle($item->name());
        $options = [
            'data' => compact('item', 'active', 'catalog', 'bread')
        ];
        if (isset($_GET['quickview'])) {
            $options['page'] = 'blank';
        }
        $this->view->page($options);
    }

}

?>
