<?php

class RecaptchaController extends Controller
{
    function indexAction()
    {
        $config = $this->Recaptcha->config;
        if (filter_input(INPUT_POST, 'secret', FILTER_SANITIZE_STRING) && filter_input(INPUT_POST, 'sitekey', FILTER_SANITIZE_STRING)) {
            $config['secret'] = filter_input(INPUT_POST, 'secret', FILTER_SANITIZE_STRING);
            $config['sitekey'] = filter_input(INPUT_POST, 'sitekey', FILTER_SANITIZE_STRING);
            Config::save('module', $config, 'Recaptcha');
            Tools::redirect('/admin/Recaptcha', 'Настройки были сохранены', 'success');
        }
        $this->view->page(['data' => compact('config')]);
    }

}
