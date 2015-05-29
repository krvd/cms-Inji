<?php

class View extends Module {

    public $title = 'Title';
    public $template = ['name' => 'default'];
    public $libAssets = array('css' => array(), 'js' => array());
    public $dynAssets = array('css' => array(), 'js' => array());
    public $viewedContent = '';
    public $contentData = [];
    public $templatesPath = '';
    private $tmp_data = array();

    function init() {
        if (!empty(App::$cur->config['site']['name'])) {
            $this->title = App::$cur->config['site']['name'];
        }
        if (!empty($this->config[App::$cur->type]['current'])) {
            $this->template['name'] = $this->config[App::$cur->type]['current'];
        }
        $this->templatesPath = App::$cur->path . "/templates";

        $template = $this->getConfig($this->template['name']);
        if ($template) {
            $this->template = $template;
            $this->template['path'] = $this->templatesPath . '/' . $this->template['name'];
        } else {
            $this->template = [
                'name' => 'default',
                'file' => 'index.html',
                'path' => $this->templatesPath . '/default'
            ];
        }

        $this->tmp_data = array(
            'path' => $this->templatesPath . "/{$this->template['name']}/{$this->template['file']}",
            'name' => $this->template['name'],
            'module' => Module::$cur,
        );
    }

    function getConfig($templateName = '') {
        if (!$templateName) {
            $templateName = $this->template['name'];
        }
        return Config::custom($this->templatesPath . "/{$templateName}/config.php");
    }

    function getParentConfig($templateName = '') {
        if (!$templateName) {
            $templateName = !empty($this->config[App::$cur->apps->parent['type']]['current']) ? $this->config[App::$cur->apps->parent['type']]['current'] : 'default';
        }
        return App::$cur->config->custom(App::$cur->apps->parent['path'] . "/templates/{$templateName}/config.php");
    }

    function page($params = []) {
        if (empty($this->tmp_data['contentPath'])) {
            $this->tmp_data['contentPath'] = Controller::$cur->path . '/content';
        }
        if (empty($this->tmp_data['content'])) {
            $this->tmp_data['content'] = Controller::$cur->method;
        }
        $data = $this->paramsParse($params);
        if (file_exists($data['path'])) {
            $source = file_get_contents($data['path']);
            if (strpos($source, 'BODYEND') === false) {
                $source = str_replace('</body>', '{BODYEND}</body>', $source);
            }
            $this->parseSource($source);
        } else {
            $this->content();
        }
    }

    function paramsParse($params) {
        if (!$this->tmp_data['module']) {
            $this->tmp_data['module'] = Module::$cur;
        }
        $data = $this->tmp_data;
        // set template
        if (!empty($params['template'])) {
            if (file_exists($this->template['path'] . "/{$params['template']}.html")) {
                $data['file'] = $params['template'] . '.html';
                $data['path'] = $this->template['path'] . '/' . $data['file'];
            } elseif ($template = $this->getConfig($params['template'])) {
                $this->template = $template;
                $data['path'] = $this->template['path'] = $this->templatesPath . '/' . $this->template['name'] . '/' . $this->template['file'];
                $data['name'] = $this->template['name'];
                $data['file'] = $this->template['file'];
            }
        }
        //set module
        if (!empty($params['module'])) {
            $data['module'] = App::$cur->$params['module'];
        }
        //set content
        if (!empty($params['content'])) {

            $paths = [
                'template' => $this->templatesPath . '/' . $this->template['name'] . "/modules/{$data['module']->moduleName}",
                'controlelrContent' => Controller::$cur->path . '/content'
            ];

            foreach ($paths as $type => $path) {
                if (file_exists($path . '/' . $params['content'] . '.php')) {
                    $data['contentPath'] = $path;
                    $data['content'] = $params['content'];
                    break;
                }
            }
        }
        if (!empty($params['data'])) {
            $this->contentData = array_merge($this->contentData, $params['data']);
        }
        $this->tmp_data = $data;
        return $data;
    }

    function content($params = []) {

        if (App::$cur->msg && empty($this->template['noSysMesAutoShow'])) {
            App::$cur->msg->show(true);
        }

        $_params = $this->paramsParse($params);
        extract($this->contentData);
        if (!file_exists($_params['contentPath'] . '/' . $_params['content'] . '.php')) {
            echo 'Content not found';
        } else {
            include $_params['contentPath'] . '/' . $_params['content'] . '.php';
        }
    }

    private function parseRaw($source) {
        if (!$source)
            return array();

        preg_match_all("|{([^}]+)}|", $source, $result);
        return $result[1];
    }

    function parseSource($source) {
        $tags = $this->parseRaw($source);
        foreach ($tags as $rawTag) {
            $tag = explode(':', $rawTag);
            switch ($tag[0]) {
                case 'CONTENT':
                    $source = $this->cutTag($source, $rawTag);
                    $this->content();
                    break;
                case 'WIDGET':
                    $source = $this->cutTag($source, $rawTag);
                    $this->widget($tag[1], ['params' => array_slice($tag, 2)]);
                    break;
                case 'HEAD':
                    $source = $this->cutTag($source, $rawTag);
                    $this->head();
                    break;
                case 'PAGE':
                    $source = $this->cutTag($source, $rawTag);
                    $this->page(['template' => $tag[1]]);
                    break;
                case 'BODYEND':
                    $source = $this->cutTag($source, $rawTag);
                    $this->bodyEnd();
                    break;
            }
        }
        echo $source;
    }

    function cutTag($source, $rawTag) {
        $pos = strpos($source, $rawTag) - 1;
        echo substr($source, 0, $pos);
        return substr($source, ( $pos + strlen($rawTag) + 2));
    }

    function bodyEnd() {
        $options = [
            'scripts' => [],
            'styles' => [],
        ];
        $scripts = [];
        if (!empty($this->libAssets['js'])) {
            foreach ($this->libAssets['js'] as $js) {
                $href = $this->getHref('js', $js);
                if (!$href)
                    continue;

                $scripts[] = $href;
            }
        }
        if (!empty(Inji::$config['assets']['js'])) {
            foreach (Inji::$config['assets']['js'] as $js) {
                if (is_array($js)) {
                    $asset = $js;
                } else {
                    $asset = [];
                }
                $asset['file'] = $this->getHref('js', $js);
                if (!$asset['file'])
                    continue;
                $scripts[] = $asset;
            }
        }
        if (!empty($this->dynAssets['js'])) {
            foreach ($this->dynAssets['js'] as $js) {
                if (is_array($js)) {
                    $asset = $js;
                } else {
                    $asset = [];
                }
                $asset['file'] = $this->getHref('js', $js);
                if (!$asset['file'])
                    continue;
                $scripts[] = $asset;
            }
        }
        if (!empty($this->template['js'])) {
            foreach ($this->template['js'] as $js) {
                if (strpos($js, '//') !== false)
                    $href = $js;
                else
                    $href = App::$cur->templatesPath . "/{$this->template['name']}/js/{$js}";
                $scripts[] = $href;
            }
        }
        $options['scripts'] = $scripts;
        $options['appRoot'] = App::$cur->type == 'app' ? '/' : '/' . App::$cur->name . '/';
        $this->widget('View\bodyEnd', compact('options'));
    }

    function getHref($type, $params) {
        if (is_string($params)) {
            return (App::$cur->type != 'app' ? '/' . App::$cur->name : '' ) . $params . "?" . rand(0, 100);
        } elseif (empty($params['template']) && !empty($params['file'])) {
            return (App::$cur->type != 'app' ? '/' . App::$cur->name : '' ) . $params['file'] . "?" . rand(0, 100);
        } elseif (!empty($params['template']) && !empty($params['file'])) {
            return App::$cur->templatesPath . "/{$this->template['name']}/{$type}/{$js['file']}?" . rand(0, 100);
        }
        return '';
    }

    function checkNeedLibs() {
        if (!empty($this->template['libs'])) {
            foreach ($this->template['libs'] as $libName) {
                App::$cur->libs->loadLib($libName);
            }
        }
        foreach ($this->dynAssets['js'] as $asset) {
            if (is_array($asset) && !empty($asset['libs'])) {
                foreach ($asset['libs'] as $libName) {
                    App::$cur->libs->loadLib($libName);
                }
            }
        }
    }

    function head() {

        echo "<title>{$this->title}</title>\n";

        if (!empty($this->template['favicon']) && file_exists($this->template['path'] . "/{$this->template['favicon']}"))
            echo "        <link rel='shortcut icon' href='/templates/{$this->template['name']}/{$this->template['favicon']}' />";
        elseif (file_exists(App::$cur->path . '/static/images/favicon.ico'))
            echo "        <link rel='shortcut icon' href='/static/images/favicon.ico' />";


        if (!empty(App::$cur->Config->app['site']['keywords'])) {
            echo "\n        <meta name='keywords' content='" . App::$cur->Config->site['site']['keywords'] . "' />";
        }
        if (!empty(App::$cur->Config->app['site']['description'])) {
            echo "\n        <meta name='description' content='" . App::$cur->Config->site['site']['description'] . "' />";
        }
        if (!empty(App::$cur->Config->app['site']['metatags'])) {
            foreach (App::$cur->Config->app['site']['metatags'] as $meta)
                echo "\n        <meta name='{$meta['name']}' content='{$meta['content']}' />";
        }

        $this->checkNeedLibs();

        if (!empty($this->libAssets['css'])) {
            foreach ($this->libAssets['css'] as $css) {
                if (strpos($css, '//') !== false)
                    $href = $css;
                else
                    $href = (App::$cur->type != 'app' ? '/' . App::$cur->name : '' ) . $css;
                echo "\n        <link href='{$href}' rel='stylesheet' type='text/css' />";
            }
        }
        if (!empty($this->template['css'])) {
            foreach ($this->template['css'] as $css) {
                if (strpos($css, '://') !== false)
                    $href = $css;
                else
                    $href = App::$cur->templatesPath . "/{$this->template['name']}/css/{$css}";
                echo "\n        <link href='{$href}' rel='stylesheet' type='text/css' />";
            }
        }
        if (!empty($this->dynAssets['css'])) {
            foreach ($this->dynAssets['css'] as $css) {
                if (strpos($css, '//') !== false)
                    $href = $css;
                else
                    $href = (App::$cur->type != 'app' ? '/' . App::$cur->name : '' ) . $css;
                echo "\n        <link href='{$href}' rel='stylesheet' type='text/css' />";
            }
        }

        echo "\n        <script src='" . (App::$cur->type != 'app' ? '/' . App::$cur->name : '' ) . "/static/system/js/Inji.js'></script>";
    }

    function timegen() {
        $this->current_function = 'TIMEGEN';
        echo round(( microtime(true) - INJI_TIME_START), 4);
    }

    function customAsset($type, $asset, $lib = false) {
        if (!$lib) {
            $this->dynAssets[$type][] = $asset;
        } else {
            $this->libAssets[$type][] = $asset;
        }
    }

    function setTitle($title, $add = true) {
        if ($add && !empty(App::$cur->Config->app['site']['name'])) {
            $this->title = $title . ' - ' . App::$cur->Config->app['site']['name'];
        } else {
            $this->title = $title;
        }
    }

    function widget($widgetName, $params = []) {
        if ($params && is_array($params)) {
            extract($params);
        }
        if (strpos($widgetName, '\\')) {
            $widgetName = explode('\\', $widgetName);
            if (App::$cur->$widgetName[0] && file_exists(App::$cur->$widgetName[0]->path . '/widgets/' . $widgetName[1] . '.php')) {
                include App::$cur->$widgetName[0]->path . '/widgets/' . $widgetName[1] . '.php';
            }
        } elseif (file_exists($this->templatesPath . '/' . $this->template['name'] . '/widgets/' . $widgetName . '.php')) {
            include $this->templatesPath . '/' . $this->template['name'] . '/widgets/' . $widgetName . '.php';
        } elseif (file_exists(App::$cur->path . '/widgets/' . $widgetName . '.php')) {
            include App::$cur->path . '/widgets/' . $widgetName . '.php';
        }
    }

}

?>
