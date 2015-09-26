<?php

/**
 * Callbacks controller
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class CallbacksController extends Controller
{
    function viewAction($callbackId)
    {
        $callback = Callbacks\Callback::get((int) $callbackId);
        if (!$callback) {
            Tools::header(404);
            Tools::redirect('/', 'Отзыв не найден', 'danger');
        }
        $this->view->setTitle('Отзыв: ' . $callback->name);
        $this->view->page(['data' => compact('callback')]);
    }

}
