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
            $templateName = !empty($this->config[App::$primary->type]['current']) ? $this->config[App::$primary->type]['current'] : 'default';
        }
        return Config::custom(App::$primary->path . "/templates/{$templateName}/config.php");
    }

    function page($params = []) {
        if (empty($this->tmp_data['contentPath'])) {
            $this->tmp_data['contentPath'] = Controller::$cur->path . '/content';
        }
        if (empty($this->tmp_data['content'])) {
            $this->tmp_data['content'] = Controller::$cur->method;
            $paths = $this->getContentPaths();
            foreach ($paths as $type => $path) {
                if (file_exists($path . '/' . $this->tmp_data['content'] . '.php')) {
                    $this->tmp_data['contentPath'] = $path;
                    $this->tmp_data['content'] = $this->tmp_data['content'];
                    break;
                }
            }
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
            $this->tmp_data['module'] = $data['module'] = App::$cur->$params['module'];
        }
        //set content
        if (!empty($params['content'])) {
            $paths = $this->getContentPaths();
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

    function getContentPaths() {
        $paths = [
            'template' => $this->templatesPath . '/' . $this->template['name'] . "/modules/{$this->tmp_data['module']->moduleName}",
            'controllerContent' => Controller::$cur->path . '/content'
        ];
        return $paths;
    }

    function content($params = []) {

        if (empty($this->template['noSysMsgAutoShow'])) {
            Msg::show(true);
        }

        $_params = $this->paramsParse($params);

        if (!file_exists($_params['contentPath'] . '/' . $_params['content'] . '.php')) {
            echo 'Content not found';
        } else {
            extract($this->contentData);
            include $_params['contentPath'] . '/' . $_params['content'] . '.php';
        }
    }

    function parentContent() {
        $paths = $this->getContentPaths();
        $data = [];
        foreach ($paths as $type => $path) {
            if (file_exists($path . '/' . $this->tmp_data['content'] . '.php')) {
                if ($path == $this->tmp_data['contentPath']) {
                    continue;
                }
                $data['contentPath'] = $path;
                $data['content'] = $this->tmp_data['content'];
                break;
            }
        }
        if (!$data) {
            echo 'Content not found';
        } else {
            extract($this->contentData);
            include $data['contentPath'] . '/' . $data['content'] . '.php';
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
                case 'TITLE':
                    $source = $this->cutTag($source, $rawTag);
                    echo $this->title;
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
        if (!empty(Inji::$config['assets']['js'])) {
            foreach (Inji::$config['assets']['js'] as $js) {
                $this->customAsset('js', $js);
            }
        }

        $this->checkNeedLibs();

        if (!empty($this->libAssets['css'])) {
            $this->renderCss($this->libAssets['css'], 'libs');
        }
        if (!empty($this->template['css'])) {
            $this->renderCss($this->template['css'], 'template');
        }
        if (!empty($this->dynAssets['css'])) {
            $this->renderCss($this->dynAssets['css'], 'custom');
        }
        echo "\n        <script src='" . (App::$cur->type != 'app' ? '/' . App::$cur->name : '' ) . "/static/system/js/Inji.js'></script>";
    }

    function renderCss($cssArray, $type = 'custom') {
        switch ($type) {
            case'libs':
                foreach ($cssArray as $css) {
                    if (is_array($css)) {
                        $this->renderCss($css, $type);
                        continue;
                    }
                    if (strpos($css, '//') !== false)
                        $href = $css;
                    else
                        $href = (App::$cur->type != 'app' ? '/' . App::$cur->name : '' ) . $css;
                    echo "\n        <link href='{$href}' rel='stylesheet' type='text/css' />";
                }
                break;
            case'template':
                foreach ($cssArray as $css) {
                    if (is_array($css)) {
                        $this->renderCss($css, $type);
                        continue;
                    }
                    if (strpos($css, '://') !== false)
                        $href = $css;
                    else
                        $href = App::$cur->templatesPath . "/{$this->template['name']}/css/{$css}";
                    echo "\n        <link href='{$href}' rel='stylesheet' type='text/css' />";
                }
                break;
            case 'custom':
                foreach ($cssArray as $css) {
                    if (is_array($css)) {
                        $this->renderCss($css, $type);
                        continue;
                    }
                    if (strpos($css, '//') !== false)
                        $href = $css;
                    else
                        $href = (App::$cur->type != 'app' ? '/' . App::$cur->name : '' ) . $css;
                    echo "\n        <link href='{$href}' rel='stylesheet' type='text/css' />";
                }
                break;
        }
    }

    function bodyEnd() {
        $options = [
            'scripts' => [],
            'styles' => [],
        ];
        if (!empty($this->libAssets['js'])) {
            $this->genScriptArray($this->libAssets['js'], 'libs', $options['scripts']);
        }
        if (!empty($this->dynAssets['js'])) {
            $this->genScriptArray($this->dynAssets['js'], 'custom', $options['scripts']);
        }
        if (!empty($this->template['js'])) {
            $this->genScriptArray($this->template['js'], 'template', $options['scripts']);
        }
        $options['appRoot'] = App::$cur->type == 'app' ? '/' : '/' . App::$cur->name . '/';
        $this->widget('View\bodyEnd', compact('options'));
    }

    function genScriptArray($jsArray, $type = 'custom', &$resultArray) {
        switch ($type) {
            case 'libs':
                foreach ($jsArray as $js) {
                    if (is_array($js)) {
                        $this->genScriptArray($js, $type, $resultArray);
                        continue;
                    }
                    $href = $this->getHref('js', $js);
                    if (!$href)
                        continue;

                    $resultArray[] = $href;
                }
                break;
            case'template':
                foreach ($jsArray as $js) {
                    if (is_array($js)) {
                        $this->genScriptArray($js, $type, $resultArray);
                        continue;
                    }
                    if (strpos($js, '//') !== false)
                        $href = $js;
                    else
                        $href = App::$cur->templatesPath . "/{$this->template['name']}/js/{$js}";
                    $resultArray[] = $href;
                }
                break;
            case 'custom':
                foreach ($jsArray as $js) {
                    if (is_array($js)) {
                        if (!empty($js[0]) && is_array($js[0])) {
                            $this->genScriptArray($js, $type, $resultArray);
                            continue;
                        }
                        $asset = $js;
                    } else {
                        $asset = [];
                    }
                    $asset['file'] = $this->getHref('js', $js);
                    if (!$asset['file'])
                        continue;
                    $resultArray[] = $asset;
                }
                break;
        }
    }

    function timegen() {
        $this->current_function = 'TIMEGEN';
        echo round(( microtime(true) - INJI_TIME_START), 4);
    }

    function customAsset($type, $asset, $lib = false) {
        if (!$lib) {
            $this->dynAssets[$type][] = $asset;
        } else {
            $this->libAssets[$type][$lib][] = $asset;
        }
    }

    function setTitle($title, $add = true) {
        if ($add && !empty(App::$cur->Config->app['site']['name'])) {
            $this->title = $title . ' - ' . App::$cur->Config->app['site']['name'];
        } else {
            $this->title = $title;
        }
    }

    function widget($_widgetName, $_params = []) {

        $_paths = $this->getWidgetPaths($_widgetName);
        foreach ($_paths as $_path) {
            if (file_exists($_path)) {
                if ($_params && is_array($_params)) {
                    extract($_params);
                }
                include $_path;
                break;
            }
        }
    }

    function getWidgetPaths($widgetName) {
        $paths = [];
        if (strpos($widgetName, '\\')) {
            $widgetName = explode('\\', $widgetName);

            $paths['templatePath_widgetDir'] = $this->templatesPath . '/' . $this->template['name'] . '/widgets/' . $widgetName[0] . '/' . $widgetName[1] . '/' . $widgetName[1] . '.php';
            $paths['templatePath'] = $this->templatesPath . '/' . $this->template['name'] . '/widgets/' . $widgetName[0] . '/' . $widgetName[1] . '.php';

            $modulePaths = Module::getModulePaths(ucfirst($widgetName[0]));
            foreach ($modulePaths as $pathName => $path) {
                $paths[$pathName . '_widgetDir'] = $path . '/widgets/' . $widgetName[1] . '/' . $widgetName[1] . '.php';
                $paths[$pathName] = $path . '/widgets/' . $widgetName[1] . '.php';
            }
            return $paths;
        } else {
            $paths['templatePath_widgetDir'] = $this->templatesPath . '/' . $this->template['name'] . '/widgets/' . $widgetName . '/' . $widgetName . '.php';
            $paths['templatePath'] = $this->templatesPath . '/' . $this->template['name'] . '/widgets/' . $widgetName . '.php';

            $paths['curAppPath_widgetDir'] = App::$cur->path . '/widgets/' . $widgetName . '/' . $widgetName . '.php';
            $paths['curAppPath'] = App::$cur->path . '/widgets/' . $widgetName . '.php';

            $paths['systemPath_widgetDir'] = INJI_SYSTEM_DIR . '/widgets/' . $widgetName . '/' . $widgetName . '.php';
            $paths['systemPath'] = INJI_SYSTEM_DIR . '/widgets/' . $widgetName . '.php';
        }
        return $paths;
    }

}

?>
