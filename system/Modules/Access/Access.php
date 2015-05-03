<?php

class Access extends Module {

    function getDeniedRedirect($app = false) {
        if (!$app) {
            $app = Inji::app()->curApp['type'];
        }
        if (!empty($this->config[Inji::app()->curApp['type']]['denied_redirect']))
            return $this->config[Inji::app()->curApp['type']]['denied_redirect'];

        return '/';
    }

}
