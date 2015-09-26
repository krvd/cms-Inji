<?php

namespace Ui;

class Pages extends \Object
{
    public $data = [];
    public $options = [];
    public $params = [];

    function __construct($data, $options = [])
    {
        $this->data = $data;
        $this->options = $options;

        $this->params['limit'] = !empty($this->data['limit']) ? (int) $this->data['limit'] : (!empty($this->options['limit']) ? (int) $this->options['limit'] : 10);
        if ($this->params['limit'] <= 0) {
            $this->params['limit'] = 10;
        }

        if ($this->options['count'] <= 0) {
            $this->params['page'] = 0;
            $this->params['pages'] = 0;
            $this->params['start'] = 0;
        } else {
            $this->params['pages'] = ceil($this->options['count'] / $this->params['limit']);
            $this->params['page'] = 1;
            if (!empty($this->data['page'])) {
                $this->params['page'] = (int) $this->data['page'];
                if ($this->params['page'] <= 0)
                    $this->params['page'] = 1;
                elseif ($this->params['page'] > ceil($this->options['count'] / $this->params['limit']))
                    $this->params['page'] = ceil($this->options['count'] / $this->params['limit']);
            }

            $this->params['start'] = $this->params['page'] * $this->params['limit'] - $this->params['limit'];
        }
        $this->params['limit'] = $this->params['limit'];
        if (empty($this->options['url'])) {
            $this->options['url'] = '';
        }
    }

    function draw($class = 'pagination pagination-centered margin-none pagination-sm')
    {
        $getArr = $this->data;
        $getArr['limit'] = $this->params['limit'];
        \App::$cur->view->widget('Ui\Pages/pages', ['class' => $class, 'pagesInstance' => $this, 'getArr' => $getArr]);
    }

}
