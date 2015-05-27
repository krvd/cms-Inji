<?php

class Access extends Module {

    function getDeniedRedirect($app = false) {
        if (!$app) {
            $app = App::$cur->type;
        }
        if (!empty($this->config[App::$cur->type]['denied_redirect']))
            return $this->config[App::$cur->type]['denied_redirect'];

        return '/';
    }

}
