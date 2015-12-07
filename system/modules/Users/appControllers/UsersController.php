<?php

/**
 * Users app controller
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class UsersController extends Controller
{
    function indexAction()
    {
        Tools::redirect('/users/cabinet/profile');
    }

    function cabinetAction($activeSection = '')
    {
        $bread = [];

        $sections = $this->module->getSnippets('cabinetSection');
        if (!empty($sections[$activeSection]['name'])) {
            $this->view->setTitle($sections[$activeSection]['name'] . ' - Личный кабинет');
            $bread[] = ['text' => 'Личный кабинет', 'href' => '/users/cabinet'];
            $bread[] = ['text' => $sections[$activeSection]['name']];
        } else {
            $this->view->setTitle('Личный кабинет');
            $bread[] = ['text' => 'Личный кабинет'];
        }
        $this->view->page(['data' => compact('widgets', 'sections', 'activeSection', 'bread')]);
    }

    function loginAction()
    {
        $this->view->setTitle('Авторизация');
        $bread = [];
        $bread[] = ['text' => 'Авторизация'];
        $this->view->page(['data' => compact('bread')]);
    }

    function registrationAction()
    {
        $this->view->setTitle('Регистрация');
        if (Users\User::$cur->user_id) {
            Tools::redirect('/', 'Вы уже зарегистрированы');
        }
        if (!empty($_POST)) {
            $error = false;
            if ($this->Recaptcha) {
                $response = $this->Recaptcha->check($_POST['g-recaptcha-response']);
                if ($response) {
                    if (!$response->success) {
                        Msg::add('Вы не прошли проверку на робота', 'danger');
                        $error = true;
                    }
                } else {
                    Msg::add('Произошла ошибка, попробуйте ещё раз');
                    $error = true;
                }
            }
            if (!$error) {
                if ($this->Users->registration($_POST)) {
                    Tools::redirect('/');
                }
            }
        }
        $this->view->setTitle('Регистрация');
        $bread = [];
        $bread[] = ['text' => 'Регистрация'];
        $this->view->page(['data' => compact('bread')]);
    }

    function activationAction($userId = 0, $hash = '')
    {
        $user = \Users\User::get((int) $userId);
        if (!$user || $user->activation !== (string) $hash) {
            Tools::redirect('/', 'Во время активации произошли ошибки', 'danger');
        }
        $user->activation = '';
        $user->save();
        Tools::redirect('/', 'Вы успешно активировали ваш аккаунт, теперь вы можете войти');
    }

    function getPartnerInfoAction($userId = 0)
    {
        $userId = (int) $userId;
        $result = new \Server\Result();
        if (!$userId) {
            $result->success = FALSE;
            $result->content = 'Не указан пользователь';
            $result->send();
        }
        $partners = App::$cur->users->getUserPartners(Users\User::$cur, 8);
        if (empty($partners['users'][$userId])) {
            $result->success = FALSE;
            $result->content = 'Этот пользователь не находится в вашей структуре';
            $result->send();
        }
        $user = $partners['users'][$userId];
        ob_start();
        echo "id:{$user->id}<br />";
        echo "E-mail: <a href='mailto:{$user->mail}'>{$user->mail}</a>";
        $rewards = Money\Reward::getList(['where' => ['active', 1]]);
        $levelTypes = [
            'procent' => 'Процент',
            'amount' => 'Сумма',
        ];
        $itemTypes = [
            'event' => 'Событие'
        ];
        foreach ($rewards as $reward) {
            foreach ($reward->conditions as $condition) {
                $complete = $condition->checkComplete($userId);
                ?>
                <h5 class="<?= $complete ? 'text-success' : 'text-danger'; ?>"><?= $condition->name(); ?></h5>
                <ul>
                  <?php
                  foreach ($condition->items as $item) {
                      $itemComplete = $item->checkComplete($userId);
                      switch ($item->type) {
                          case 'event':
                              $name = \Events\Event::get($item->value, 'event')->name();
                              break;
                      }
                      ?>
                      <li> 
                        <b class="<?= $itemComplete ? 'text-success' : 'text-danger'; ?>"><?= $name; ?> <?= $item->recivedCount($userId); ?></b>/<?= $item->count; ?> <br />
                      </li>
                      <?php
                  }
                  ?>
                </ul>
                <?php
            }
        }
        $result->content = ob_get_contents();
        ob_end_clean();
        $result->send();
    }

}
