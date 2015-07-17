<?php

class UserForms extends \Module {

    function init() {
        \App::$cur->view->customAsset('js', '/static/moduleAsset/UserForms/js/formCatcher.js');
        if (!empty($_POST['UserForms'])) {
            foreach ($_POST['UserForms'] as $form_id => $inputs) {
                $form = \UserForms\Form::get((int) $form_id);
                if (!$form)
                    continue;
                $formRecive = new \UserForms\Recive();
                $formRecive->user_id = (int) \Users\User::$cur->id;
                $formRecive->form_id = (int) $form_id;
                $data = [];
                foreach ($form->inputs as $input) {
                    if (isset($inputs['input' . $input->id])) {
                        $data['input' . $input->id] = htmlspecialchars($inputs['input' . $input->id]);
                    } else {
                        $data['input' . $input->id] = '';
                    }
                }
                $formRecive->data = json_encode($data);
                $formRecive->save();
            }

            if (!empty(App::$cur->config['site']['email'])) {
                $text = '';
                foreach ($form->inputs as $input) {
                    if (isset($inputs['input' . $input->id])) {
                        $text .="<b>{$input->label}:</b> " . htmlspecialchars($inputs['input' . $input->id]) . "<br />";
                    }
                }
                if ($text) {
                    $text = 'Дата получения по серверному времени: ' . date('Y-m-d H:i:s') . '<br />Заполненые поля:<br />' . $text;
                    Tools::sendMail('noreply@' . INJI_DOMAIN_NAME, App::$cur->config['site']['email'], $form->title, $text);
                }
            }
            Tools::redirect($_SERVER['REQUEST_URI'], 'Ваша форма была успешно отправлена', 'success');
        }
    }

    function formData($item) {
        $inputs = json_decode($item->data, true);
        $text = '';
        foreach ($item->form->inputs as $input) {
            if (isset($inputs['input' . $input->id])) {
                $text .= "{$input->label}: " . htmlspecialchars($inputs['input' . $input->id]) . "<br />";
            }
        }
        return $text;
    }

}
