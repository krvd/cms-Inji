<?php

/**
 * View module
 *
 * Rendering pages, contents and widgets
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class View extends Module {

    public $title = 'No title';
    public $template = null;
    public $libAssets = ['css' => [], 'js' => []];
    public $dynAssets = ['css' => [], 'js' => []];
    public $dynMetas = [];
    public $viewedContent = '';
    public $contentData = [];
    public $templatesPath = '';

    function init() {
        if (!empty(App::$cur->config['site']['name'])) {
            $this->title = App::$cur->config['site']['name'];
        }
        $this->resolveTemplate();
    }

    function resolveTemplate() {
        $templateName = 'default';
        if (!empty($this->config[App::$cur->type]['current'])) {
            $templateName = $this->config[App::$cur->type]['current'];
            if (!empty($this->config[App::$cur->type]['installed'][$templateName]['location'])) {
                $this->templatesPath = App::$primary->path . "/templates";
            }
        }
        if (!$this->templatesPath) {
            $this->templatesPath = $this->app->path . "/templates";
        }

        $this->template = \View\Template::get($templateName, $this->app, $this->templatesPath);
        if (!$this->template) {
            $this->template = new \View\Template([
                'name' => 'default',
                'path' => $this->templatesPath . '/default',
                'app' => $this->app
            ]);
        }
    }

    function page($params = []) {

        $this->paramsParse($params);
        if (file_exists($this->template->pagePath)) {
            $source = file_get_contents($this->template->pagePath);
            if (strpos($source, 'BODYEND') === false) {
                $source = str_replace('</body>', '{BODYEND}</body>', $source);
            }
            $this->parseSource($source);
        } else {
            $this->content();
        }
    }

    function paramsParse($params) {
        // set template
        if (!empty($params['template']) && $params['template'] != 'current') {
            $this->template = \View\Template::get($params['template']);
        }
        //set page
        if (!empty($params['page']) && $params['page'] != 'current') {
            $this->template->setPage($params['page']);
        }
        //set module
        if (!empty($params['module'])) {
            $this->template->setModule($params['module']);
        }
        //set content
        if (!empty($params['content'])) {
            $this->template->setContent($params['content']);
        } elseif (!$this->template->contentPath) {
            $this->template->setContent();
        }
        //set data
        if (!empty($params['data'])) {
            $this->contentData = array_merge($this->contentData, $params['data']);
        }
    }

    function content($params = []) {

        $this->paramsParse($params);

        if (empty($this->template->config['noSysMsgAutoShow'])) {
            Msg::show(true);
        }
        if (!file_exists($this->template->contentPath)) {
            echo 'Content not found';
        } else {
            extract($this->contentData);
            include $this->template->contentPath;
        }
    }

    function parentContent($contentName = '') {
        if (!$contentName) {
            $contentName = $this->template->content;
        }

        $paths = $this->template->getContentPaths($contentName);

        $data = [];
        foreach ($paths as $type => $path) {
            if (file_exists($path)) {
                if ($path == $this->template->contentPath) {
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
            include $data['contentPath'];
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
                    $this->page(['page' => $tag[1]]);
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
            $href = App::$cur->templatesPath . "/{$this->template->name}/{$type}/{$js['file']}";
        }
        return $href;
    }

    function checkNeedLibs() {
        if (!empty($this->template->config['libs'])) {
            foreach ($this->template->config['libs'] as $libName) {
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

        if (!empty($this->template->config['favicon']) && file_exists($this->template->path . "/{$this->template->config['favicon']}"))
            echo "        <link rel='shortcut icon' href='/templates/{$this->template->name}/{$this->template->config['favicon']}' />";
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
        if (!empty($this->template->config['css'])) {
            $this->ResolveCssHref($this->template->config['css'], 'template', $css);
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
                        $href = App::$cur->templatesPath . "/{$this->template->name}/css/{$css}";
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
        if (!empty($this->template->config['js'])) {
            $this->genScriptArray($this->template->config['js'], 'template', $scripts);
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
                        $href = App::$cur->templatesPath . "/{$this->template->name}/js/{$js}";
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

            $paths['templatePath_widgetDir'] = $this->templatesPath . '/' . $this->template->name . '/widgets/' . $widgetName[0] . '/' . $widgetName[1] . '/' . $widgetName[1] . '.php';
            $paths['templatePath'] = $this->templatesPath . '/' . $this->template->name . '/widgets/' . $widgetName[0] . '/' . $widgetName[1] . '.php';

            $modulePaths = Module::getModulePaths(ucfirst($widgetName[0]));
            foreach ($modulePaths as $pathName => $path) {
                $paths[$pathName . '_widgetDir'] = $path . '/widgets/' . $widgetName[1] . '/' . $widgetName[1] . '.php';
                $paths[$pathName] = $path . '/widgets/' . $widgetName[1] . '.php';
            }
            return $paths;
        } else {
            $paths['templatePath_widgetDir'] = $this->templatesPath . '/' . $this->template->name . '/widgets/' . $widgetName . '/' . $widgetName . '.php';
            $paths['templatePath'] = $this->templatesPath . '/' . $this->template->name . '/widgets/' . $widgetName . '.php';

            $paths['curAppPath_widgetDir'] = App::$cur->path . '/widgets/' . $widgetName . '/' . $widgetName . '.php';
            $paths['curAppPath'] = App::$cur->path . '/widgets/' . $widgetName . '.php';

            $paths['systemPath_widgetDir'] = INJI_SYSTEM_DIR . '/widgets/' . $widgetName . '/' . $widgetName . '.php';
            $paths['systemPath'] = INJI_SYSTEM_DIR . '/widgets/' . $widgetName . '.php';
        }
        return $paths;
    }

}

?>
