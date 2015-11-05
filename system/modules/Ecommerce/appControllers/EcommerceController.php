<?php

/**
 * Ecommerce controller
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class ecommerceController extends Controller
{
    function buyCardAction()
    {
        $this->view->setTitle('Покупка карты');
        $bread = [];
        $bread[] = ['text' => 'Покупка карты'];
        if (!empty($_POST) && !empty($_POST['card_id'])) {
            $error = false;
            $card = \Ecommerce\Card::get((int) $_POST['card_id']);
            if (!$card) {
                $error = true;
                Msg::add('Такой карты не существует', 'danger');
            }

            if (!Users\User::$cur->id) {
                $user_id = $this->Users->registration($_POST, true);
                if (!$user_id) {
                    $error = true;
                } else {
                    $user = Users\User::get($user_id);
                }
            } else {
                $user = Users\User::$cur;
            }
            $userCard = \Ecommerce\Card\Item::get([['card_id', $card->id], ['user_id', $user->id]]);
            if ($userCard) {
                $error = true;
                Msg::add('У вас уже есть такая карта', 'danger');
            }

            $fields = \Ecommerce\UserAdds\Field::getList();
            foreach ($fields as $field) {
                if (empty($_POST['userAdds']['fields'][$field->id]) && $field->required) {
                    $error = 1;
                    Msg::add('Вы не указали: ' . $field->name);
                }
            }
            if (!$error) {
                $cart = new \Ecommerce\Cart();
                $cart->user_id = $user->user_id;
                $cart->useradds_id = $userAdds;
                $cart->cart_status_id = 2;
                $cart->comment = htmlspecialchars($_POST['comment']);
                $cart->date_status = date('Y-m-d H:i:s');
                $cart->complete_data = date('Y-m-d H:i:s');
                if (!empty($_SESSION['cart']['cart_id'])) {
                    $curCart = Ecommerce\Cart::get($_SESSION['cart']['cart_id']);
                    $cart->card_item_id = $cardItem->id;
                }
                $cart->save();

                $this->module->parseFields($_POST['userAdds']['fields'], $cart);

                $cardItem = new \Ecommerce\Card\Item();
                $cardItem->card_id = $card->id;
                $cardItem->user_id = $user->id;
                $cardItem->save();

                $extra = new \Ecommerce\Cart\Extra();
                $extra->name = $card->name;
                $extra->price = $card->price;
                $extra->count = 1;
                $extra->cart_id = $cart->id;
                $extra->info = 'card:' . $card->id . '|cardItem:' . $cardItem->id;
                $extra->save();
                Tools::redirect('/ecommerce/cart/success');
            }
        }
        $this->view->page(['data' => compact('bread')]);
    }

    function cabinetAction()
    {
        $bread = [];
        $bread[] = ['text' => 'Каталог', 'href' => '/ecommerce/'];
        $bread[] = ['text' => 'Кабинет'];
        $this->view->setTitle('Кабинет');
        $this->view->page(['data'=>compact('bread')]);
    }

    function autoCompleteAction()
    {
        $return = Cache::get('itemsAutocomplete');
        if (!$return) {
            $count = $this->ecommerce->getItemsCount();
            $items = $this->ecommerce->getItems();
            $return = [];
            foreach ($items as $item) {
                $return[] = ['name' => $item->name(), 'search' => $item->search_index . ' ' . $item->name];
            }
            $return = json_encode($return);
            Cache::set('itemsAutocomplete', [], $return);
        }
        echo $return;
    }

    function indexAction()
    {
        Tools::redirect('/ecommerce/itemList');
    }

    function itemListAction($category_id = 0)
    {
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
            $sort = ['name' => 'asc'];
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

    function viewAction($id = '')
    {
        $item = \Ecommerce\Item::get((int) $id);
        if (!$item) {
            $this->url->redirect('/ecommerce/', 'Такой товар не найден');
        }
        $active = $item->category_id;
        $catalog = $item->category;
        $bread = [];
        $bread[] = ['text' => 'Каталог', 'href' => '/ecommerce'];

        $catalogIds = array_values(array_filter(explode('/', $item->tree_path)));
        foreach ($catalogIds as $id) {
            $cat = Ecommerce\Category::get($id);
            $bread[] = ['text' => $cat->name, 'href' => '/ecommerce/itemList/' . $cat->id];
        }
        $bread[] = ['text' => $item->name()];
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
