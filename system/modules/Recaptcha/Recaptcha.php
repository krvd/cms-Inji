<?php

class Recaptcha extends Module {

    function init() {
        App::$cur->view->customAsset('js', 'https://www.google.com/recaptcha/api.js');
    }

    function show() {
        if (!$this->config['sitekey']) {
            echo 'SiteKey not set for reCAPTCHA';
        } else {
            echo "<div class='g-recaptcha' data-sitekey='{$this->config['sitekey']}'></div>";
        }
    }

    function check($gResponse) {
        $data['secret'] = $this->config['secret'];
        $data['response'] = $gResponse;
        $response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?' . http_build_query($data));
        if ($response) {
            return json_decode($response);
        }
        return false;
    }

}
