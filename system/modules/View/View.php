<?php

class View extends Module {

    public $title = 'Title';
    public $template = ['name' => 'default'];
    public $libAssets = array('css' => array(), 'js' => array());
    public $dynAssets = array('css' => array(), 'js' => array());
    public $dynMetas = [];
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
            if (!empty($this->config[App::$cur->type]['installed'][$this->template['name']]['location'])) {
                $this->templatesPath = App::$primary->path . "/templates";
            }
        }
        if (!$this->templatesPath) {
            $this->templatesPath = App::$cur->path . "/templates";
        }
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
        if (empty($this->tmp_data['module'])) {
            $this->tmp_data['module'] = Module::$cur;
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
        if (empty($this->tmp_data['contentPath'])) {
            $this->tmp_data['contentPath'] = Controller::$cur->path . '/content';
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
            'appControllerContent' => Controller::$cur->app->path . '/modules/' . Controller::$cur->module->moduleName . '/' . Controller::$cur->app->type . 'Controllers/content',
            'controllerContent' => Controller::$cur->path . '/content',
            'moduleControllerContent' => Controller::$cur->module->path . '/' . Controller::$cur->module->app->type . 'Controllers/content',
            'customModuleTemplateControllerContent' => $this->templatesPath . '/' . $this->template['name'] . "/modules/" . $this->tmp_data['module']->moduleName,
            'customModuleControllerContent' => $this->tmp_data['module']->path . '/' . Controller::$cur->module->app->type . 'Controllers/content',
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

    function parentContent($contentName = '') {
        if (!$contentName) {
            $contentName = $this->tmp_data['content'];
        }
        $paths = $this->getContentPaths();
        $data = [];
        foreach ($paths as $type => $path) {
            if (file_exists($path . '/' . $contentName . '.php')) {
                if ($path == $this->tmp_data['contentPath']) {
                    continue;
                }
                $data['contentPath'] = $path;
                $data['content'] = $contentName;
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
        $href = '';
        if (is_string($params)) {
            $href = (App::$cur->type != 'app' ? '/' . App::$cur->name : '' ) . $params;
        } elseif (empty($params['template']) && !empty($params['file'])) {
            $href = (App::$cur->type != 'app' ? '/' . App::$cur->name : '' ) . $params['file'];
        } elseif (!empty($params['template']) && !empty($params['file'])) {
            $href = App::$cur->templatesPath . "/{$this->template['name']}/{$type}/{$js['file']}";
        }
        return $href;
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

        foreach ($this->getMetaTags() as $meta) {
            echo "\n        " . Html::el('meta', $meta, '', null);
        }

        if (!empty(Inji::$config['assets']['js'])) {
            foreach (Inji::$config['assets']['js'] as $js) {
                $this->customAsset('js', $js);
            }
        }

        $this->checkNeedLibs();

        $css = $this->getCss();
        $nativeUrl = [];
        $urls = [];
        $timeStr = '';
        $cssAll = '';
        foreach ($css as $href) {
            $nativeUrl[$href] = $href;
            $urls[$href] = $path = App::$cur->staticLoader->parsePath($href);
            $timeStr.=filemtime($path);
        }

        $timeMd5 = md5($timeStr);
        if (!file_exists(App::$primary->path . '/static/cache/all' . $timeMd5 . '.css')) {
            foreach ($urls as $primaryUrl => $url) {
                $source = file_get_contents($url);
                $matches = [];
                $rootPath = substr($primaryUrl, 0, strrpos($primaryUrl, '/'));
                $levelUpPath = substr($rootPath, 0, strrpos($rootPath, '/'));

                $source = preg_replace('!url\((\'?"?)[\.]{2}!isU', 'url($1' . $levelUpPath, $source);
                $source = preg_replace('!url\((\'?"?)[\.]{1}!isU', 'url($1' . $rootPath, $source);
                $source = preg_replace('!url\(([^/]\'?"?)([^/]){1}!isU', 'url($1' . $rootPath . '/$2', $source);
                $cssAll .= $source;
            }
            Tools::createDir(App::$primary->path . '/static/cache/');
            file_put_contents(App::$primary->path . '/static/cache/all' . $timeMd5 . '.css', $cssAll);
        }
        echo "\n        <link href='/static/cache/all{$timeMd5}.css' rel='stylesheet' type='text/css' />";
        echo "\n        <script src='" . (App::$cur->type != 'app' ? '/' . App::$cur->name : '' ) . "/static/system/js/Inji.js'></script>";
    }

    function getCss() {
        $css = [];
        if (!empty($this->libAssets['css'])) {
            $this->ResolveCssHref($this->libAssets['css'], 'libs', $css);
        }
        if (!empty($this->template['css'])) {
            $this->ResolveCssHref($this->template['css'], 'template', $css);
        }
        if (!empty($this->dynAssets['css'])) {
            $this->ResolveCssHref($this->dynAssets['css'], 'custom', $css);
        }
        return $css;
    }

    function ResolveCssHref($cssArray, $type = 'custom', &$hrefs) {
        switch ($type) {
            case'libs':
                foreach ($cssArray as $css) {
                    if (is_array($css)) {
                        $this->ResolveCssHref($css, $type, $hrefs);
                        continue;
                    }
                    if (strpos($css, '//') !== false)
                        $href = $css;
                    else
                        $href = (App::$cur->type != 'app' ? '/' . App::$cur->name : '' ) . $css;
                    $hrefs[$href] = $href;
                }
                break;
            case'template':
                foreach ($cssArray as $css) {
                    if (is_array($css)) {
                        $this->ResolveCssHref($css, $type, $hrefs);
                        continue;
                    }
                    if (strpos($css, '://') !== false)
                        $href = $css;
                    else
                        $href = App::$cur->templatesPath . "/{$this->template['name']}/css/{$css}";
                    $hrefs[$href] = $href;
                }
                break;
            case 'custom':
                foreach ($cssArray as $css) {
                    if (is_array($css)) {
                        $this->ResolveCssHref($css, $type, $hrefs);
                        continue;
                    }
                    if (strpos($css, '//') !== false)
                        $href = $css;
                    else
                        $href = (App::$cur->type != 'app' ? '/' . App::$cur->name : '' ) . $css;
                    $hrefs[$href] = $href;
                }
                break;
        }
    }

    function getMetaTags() {
        $metas = [];

        if (!empty(App::$cur->Config->app['site']['keywords'])) {
            $metas['metaName:keywords'] = ['name' => 'keywords', 'content' => App::$cur->Config->site['site']['keywords']];
        }
        if (!empty(App::$cur->Config->app['site']['description'])) {
            $metas['metaName:description'] = ['name' => 'description', 'content' => App::$cur->Config->site['site']['description']];
        }
        if (!empty(App::$cur->Config->app['site']['metatags'])) {
            foreach (App::$cur->Config->app['site']['metatags'] as $meta) {
                if (!empty($meta['name'])) {
                    $metas['metaName:' . $meta['name']] = $meta;
                } elseif (!empty($meta['property'])) {
                    $metas['metaProperty:' . $meta['property']] = $meta;
                }
            }
        }
        if ($this->dynMetas) {
            $metas = array_merge($metas, $this->dynMetas);
        }
        return $metas;
    }

    function addMetaTag($meta) {
        if (!empty($meta['name'])) {
            $this->dynMetas['metaName:' . $meta['name']] = $meta;
        } elseif (!empty($meta['property'])) {
            $this->dynMetas['metaProperty:' . $meta['property']] = $meta;
        }
    }

    function bodyEnd() {
        $scripts = $this->getScripts();
        $onLoadModules = [];
        $scriptAll = '';
        $urls = [];
        $nativeUrl = [];
        $timeStr = '';
        foreach ($scripts as $script) {
            if (is_string($script)) {
                if (!empty($urls[$script]))
                    continue;
                $nativeUrl[$script] = $script;
                $urls[$script] = $path = App::$cur->staticLoader->parsePath($script);
                $timeStr.=filemtime($path);
            } elseif (!empty($script['file'])) {
                if (!empty($urls[$script['file']]))
                    continue;
                $nativeUrl[$script['file']] = $script['file'];
                $urls[$script['file']] = $path = App::$cur->staticLoader->parsePath($script['file']);
                if (!empty($script['name'])) {
                    $onLoadModules[$script['name']] = $script['name'];
                }
                $timeStr.=filemtime($path);
            }
        }

        $timeMd5 = md5($timeStr);
        if (!file_exists(App::$primary->path . '/static/cache/all' . $timeMd5 . '.js')) {
            foreach ($urls as $url) {
                $scriptAll .= file_get_contents($url);
            }
            Tools::createDir(App::$primary->path . '/static/cache/');
            file_put_contents(App::$primary->path . '/static/cache/all' . $timeMd5 . '.js', $scriptAll);
        }
        $options = [
            'scripts' => ['/static/cache/all' . $timeMd5 . '.js'],
            'compresedScripts' => $nativeUrl,
            'styles' => [],
            'appRoot' => App::$cur->type == 'app' ? '/' : '/' . App::$cur->name . '/',
            'onLoadModules' => $onLoadModules
        ];
        $this->widget('View\bodyEnd', compact('options'));
    }

    function getScripts() {
        $scripts = [];
        if (!empty($this->libAssets['js'])) {
            $this->genScriptArray($this->libAssets['js'], 'libs', $scripts);
        }
        if (!empty($this->dynAssets['js'])) {
            $this->genScriptArray($this->dynAssets['js'], 'custom', $scripts);
        }
        if (!empty($this->template['js'])) {
            $this->genScriptArray($this->template['js'], 'template', $scripts);
        }
        return $scripts;
    }

    function genScriptArray($jsArray, $type = 'custom', &$resultArray) {
        switch ($type) {
            case 'libs':
                foreach ($jsArray as $js) {
                    if (is_array($js)) {
                        $this->genScriptArray($js, $type, $resultArray);
                        continue;
                    }
                    if (strpos($js, '//') !== false)
                        $href = $js;
                    else
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
        $find = false;
        foreach ($_paths as $_path) {
            if (file_exists($_path)) {
                $find = true;
                break;
            }
        }
        $lineParams = '';
        if ($_params) {
            $paramArray = false;
            foreach ($_params as $param) {
                if (is_array($param) || is_object($param)) {
                    $paramArray = true;
                }
            }
            if (!$paramArray)
                $lineParams = ':' . implode(':', $_params);
        }

        echo "<!--start:{WIDGET:{$_widgetName}{$lineParams}}-->\n";
        if ($find) {
            if ($_params && is_array($_params)) {
                extract($_params);
            }
            include $_path;
        }
        echo "<!--end:{WIDGET:{$_widgetName}{$lineParams}}-->\n";
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
