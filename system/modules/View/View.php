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
class View extends \Module
{
    public $title = 'No title';
    public $template = null;
    public $libAssets = ['css' => [], 'js' => []];
    public $dynAssets = ['css' => [], 'js' => []];
    public $dynMetas = [];
    public $viewedContent = '';
    public $contentData = [];
    public $templatesPath = '';
    public $loadedCss = [];
    public $loadedJs = [];

    public function init()
    {
        if (!empty($this->app->config['site']['name'])) {
            $this->title = $this->app->config['site']['name'];
        }
        $this->resolveTemplate();
    }

    public function resolveTemplate()
    {
        $templateName = 'default';
        if (!empty($this->config[$this->app->type]['current'])) {
            $templateName = $this->config[$this->app->type]['current'];
            if (!empty($this->config[$this->app->type]['installed'][$templateName]['location'])) {
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

    public function page($params = [])
    {
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

    public function paramsParse($params)
    {
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

    public function content($params = [])
    {

        $this->paramsParse($params);

        if (empty($this->template->config['noSysMsgAutoShow'])) {
            Msg::show();
        }
        if (!file_exists($this->template->contentPath)) {
            echo 'Content not found';
        } else {
            extract($this->contentData);
            include $this->template->contentPath;
        }
    }

    public function parentContent($contentName = '')
    {
        if (!$contentName) {
            $contentName = $this->template->content;
        }

        $paths = $this->template->getContentPaths($contentName);

        $data = [];
        $exist = false;
        foreach ($paths as $type => $path) {
            if (substr($path, 0, strrpos($path, '/')) == substr($this->template->contentPath, 0, strrpos($this->template->contentPath, '/'))) {
                $exist = true;
                continue;
            }
            if (file_exists($path) && $exist) {
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

    private function parseRaw($source)
    {
        if (!$source)
            return [];

        preg_match_all("|{([^}]+)}|", $source, $result);
        return $result[1];
    }

    public function parseSource($source)
    {
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
                    $params = array_slice($tag, 2);
                    $this->widget($tag[1], ['params' => $params], ':' . implode(':', $params));
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

    public function cutTag($source, $rawTag)
    {
        $pos = strpos($source, $rawTag) - 1;
        echo substr($source, 0, $pos);
        return substr($source, ( $pos + strlen($rawTag) + 2));
    }

    public function getHref($type, $params)
    {
        $href = '';
        if (is_string($params)) {
            $href = ($this->app->type != 'app' ? '/' . $this->app->name : '' ) . $params;
        } elseif (empty($params['template']) && !empty($params['file'])) {
            $href = ($this->app->type != 'app' ? '/' . $this->app->name : '' ) . $params['file'];
        } elseif (!empty($params['template']) && !empty($params['file'])) {
            $href = $this->app->templatesPath . "/{$this->template->name}/{$type}/{$params['file']}";
        }
        return $href;
    }

    public function checkNeedLibs()
    {
        if (!empty($this->template->config['libs'])) {
            foreach ($this->template->config['libs'] as $libName => $libOptions) {
                if (!is_array($libOptions)) {
                    $libName = $libOptions;
                    $libOptions = [];
                }
                $this->app->libs->loadLib($libName, $libOptions);
            }
        }
        foreach ($this->dynAssets['js'] as $asset) {
            if (is_array($asset) && !empty($asset['libs'])) {
                foreach ($asset['libs'] as $libName) {
                    $this->app->libs->loadLib($libName);
                }
            }
        }
        foreach ($this->libAssets['js'] as $asset) {
            if (is_array($asset) && !empty($asset['libs'])) {
                foreach ($asset['libs'] as $libName) {
                    $this->app->libs->loadLib($libName);
                }
            }
        }
    }

    public function head()
    {

        echo "<title>{$this->title}</title>\n";

        if (!empty($this->template->config['favicon']) && file_exists($this->template->path . "/{$this->template->config['favicon']}")) {
            echo "        <link rel='shortcut icon' href='/templates/{$this->template->name}/{$this->template->config['favicon']}' />";
        } elseif (!empty($this->template->config['favicon']) && file_exists($this->app->path . "/static/images/{$this->template->config['favicon']}")) {
            echo "        <link rel='shortcut icon' href='/static/images/{$this->template->config['favicon']}' />";
        } elseif (file_exists($this->app->path . '/static/images/favicon.ico')) {
            echo "        <link rel='shortcut icon' href='/static/images/favicon.ico' />";
        }

        foreach ($this->getMetaTags() as $meta) {
            echo "\n        " . Html::el('meta', $meta, '', null);
        }

        if (!empty(Inji::$config['assets']['js'])) {
            foreach (Inji::$config['assets']['js'] as $js) {
                $this->customAsset('js', $js);
            }
        }

        $this->checkNeedLibs();
        $this->parseCss();
        echo "\n        <script src='" . Statics::file(($this->app->type != 'app' ? '/' . $this->app->name : '' ) . "/static/system/js/Inji.js") . "'></script>";
    }

    public function parseCss()
    {
        $css = $this->getCss();
        $urls = [];
        $timeStr = '';
        $cssAll = '';
        $exclude = ['^http:', '^https:', '^//'];
        foreach ($css as $href) {
            if (!empty($this->loadedCss[$href])) {
                continue;
            }
            foreach ($exclude as $item) {
                if (preg_match("!{$item}!", $href)) {
                    echo "\n        <link href='{$href}' rel='stylesheet' type='text/css' />";
                    continue;
                }
            }
            $path = $this->app->staticLoader->parsePath($href);
            if (file_exists($path)) {
                $this->loadedCss[$href] = $href;
                $urls[$href] = $path;
                $timeStr.=filemtime($path);
            } else {
                echo "\n        <link href='{$href}' rel='stylesheet' type='text/css' />";
            }
        }
        $timeMd5 = md5($timeStr);
        $cacheDir = Cache::getDir('static');
        if (!file_exists($cacheDir . '/all' . $timeMd5 . '.css')) {
            foreach ($urls as $primaryUrl => $url) {
                $source = file_get_contents($url);
                $rootPath = substr($primaryUrl, 0, strrpos($primaryUrl, '/'));
                $levelUpPath = substr($rootPath, 0, strrpos($rootPath, '/'));
                $source = preg_replace('!url\((\'?"?)[\.]{2}!isU', 'url($1' . $levelUpPath, $source);
                $source = preg_replace('!url\((\'?"?)[\.]{1}!isU', 'url($1' . $rootPath, $source);
                $source = preg_replace('#url\(([\'"]){1}(?!http|https|/|data\:)([^/])#isU', 'url($1' . $rootPath . '/$2', $source);
                $source = preg_replace('#url\((?!http|https|/|data\:|\'|")([^/])#isU', 'url(' . $rootPath . '/$1$2', $source);
                $cssAll .= $source;
            }
            file_put_contents($cacheDir . '/all' . $timeMd5 . '.css', $cssAll);
        }
        echo "\n        <link href='/{$cacheDir}/all{$timeMd5}.css' rel='stylesheet' type='text/css' />";
    }

    public function getCss()
    {
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

    public function ResolveCssHref($cssArray, $type = 'custom', &$hrefs)
    {
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
                        $href = ($this->app->type != 'app' ? '/' . $this->app->name : '' ) . $css;
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
                        $href = $this->app->templatesPath . "/{$this->template->name}/css/{$css}";
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
                        $href = ($this->app->type != 'app' ? '/' . $this->app->name : '' ) . $css;
                    $hrefs[$href] = $href;
                }
                break;
        }
    }

    public function getMetaTags()
    {
        $metas = [];

        if (!empty($this->app->config['site']['keywords'])) {
            $metas['metaName:keywords'] = ['name' => 'keywords', 'content' => $this->app->config['site']['keywords']];
        }
        if (!empty($this->app->config['site']['description'])) {
            $metas['metaName:description'] = ['name' => 'description', 'content' => $this->app->config['site']['description']];
        }
        if (!empty($this->app->config['site']['metatags'])) {
            foreach ($this->app->config['site']['metatags'] as $meta) {
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

    public function addMetaTag($meta)
    {
        if (!empty($meta['name'])) {
            $this->dynMetas['metaName:' . $meta['name']] = $meta;
        } elseif (!empty($meta['property'])) {
            $this->dynMetas['metaProperty:' . $meta['property']] = $meta;
        }
    }

    public function bodyEnd()
    {
        $this->checkNeedLibs();
        $this->parseCss();
        $scripts = $this->getScripts();
        $onLoadModules = [];
        $scriptAll = '';
        $urls = [];
        $nativeUrl = [];
        $timeStr = '';
        $noParsedScripts = [];
        foreach ($scripts as $script) {
            if (is_string($script)) {
                if (!empty($urls[$script]))
                    continue;

                $path = $this->app->staticLoader->parsePath($script);
                if (file_exists($path)) {
                    $nativeUrl[$script] = $script;
                    $urls[$script] = $path;
                    $timeStr.=filemtime($path);
                } else {
                    $noParsedScripts[$script] = $script;
                }
            } elseif (!empty($script['file'])) {
                if (!empty($urls[$script['file']]))
                    continue;

                $path = $this->app->staticLoader->parsePath($script['file']);
                if (file_exists($path)) {
                    $nativeUrl[$script['file']] = $script['file'];
                    $urls[$script['file']] = $path;
                    if (!empty($script['name'])) {
                        $onLoadModules[$script['name']] = $script['name'];
                    }
                    $timeStr.=filemtime($path);
                } else {
                    $noParsedScripts[$script] = $script;
                }
            }
        }

        $timeMd5 = md5($timeStr);
        $cacheDir = Cache::getDir('static');
        if (!file_exists($cacheDir . '/all' . $timeMd5 . '.js')) {
            foreach ($urls as $url) {
                $scriptAll .= ';' . file_get_contents($url);
            }
            file_put_contents($cacheDir . '/all' . $timeMd5 . '.js', $scriptAll);
        }
        $options = [
            'scripts' => array_values($noParsedScripts),
            'compresedScripts' => $nativeUrl,
            'styles' => [],
            'appRoot' => $this->app->type == 'app' ? '/' : '/' . $this->app->name . '/',
            'onLoadModules' => $onLoadModules
        ];
        $options['scripts'][] = '/' . $cacheDir . '/all' . $timeMd5 . '.js';
        $this->widget('View\bodyEnd', compact('options'));
    }

    public function getScripts()
    {
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

    public function genScriptArray($jsArray, $type = 'custom', &$resultArray)
    {
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
                        $href = $this->app->templatesPath . "/{$this->template->name}/js/{$js}";
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

    public function customAsset($type, $asset, $lib = false)
    {
        if (!$lib) {
            $this->dynAssets[$type][] = $asset;
        } else {
            $this->libAssets[$type][$lib][] = $asset;
        }
    }

    public function setTitle($title, $add = true)
    {
        if ($add && !empty($this->app->config['site']['name'])) {
            if ($title) {
                $this->title = $title . ' - ' . $this->app->config['site']['name'];
            } else {
                $this->title = $this->app->config['site']['name'];
            }
        } else {
            $this->title = $title;
        }
    }

    public function widget($_widgetName, $_params = [], $lineParams = null)
    {
        $_paths = $this->getWidgetPaths($_widgetName);
        $find = false;
        foreach ($_paths as $_path) {
            if (file_exists($_path)) {
                $find = true;
                break;
            }
        }
        if ($lineParams === null) {
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

    public function getWidgetPaths($widgetName)
    {
        $paths = [];
        if (strpos($widgetName, '\\')) {
            $widgetName = explode('\\', $widgetName);

            $paths['templatePath_widgetDir'] = $this->templatesPath . '/' . $this->template->name . '/widgets/' . $widgetName[0] . '/' . $widgetName[1] . '/' . $widgetName[1] . '.php';
            $paths['templatePath'] = $this->templatesPath . '/' . $this->template->name . '/widgets/' . $widgetName[0] . '/' . $widgetName[1] . '.php';
            App::$cur->$widgetName[0];
            $modulePaths = Module::getModulePaths(ucfirst($widgetName[0]));
            foreach ($modulePaths as $pathName => $path) {
                $paths[$pathName . '_widgetDir'] = $path . '/widgets/' . $widgetName[1] . '/' . $widgetName[1] . '.php';
                $paths[$pathName] = $path . '/widgets/' . $widgetName[1] . '.php';
            }
            return $paths;
        } else {
            $paths['templatePath_widgetDir'] = $this->templatesPath . '/' . $this->template->name . '/widgets/' . $widgetName . '/' . $widgetName . '.php';
            $paths['templatePath'] = $this->templatesPath . '/' . $this->template->name . '/widgets/' . $widgetName . '.php';

            $paths['curAppPath_widgetDir'] = $this->app->path . '/widgets/' . $widgetName . '/' . $widgetName . '.php';
            $paths['curAppPath'] = $this->app->path . '/widgets/' . $widgetName . '.php';

            $paths['systemPath_widgetDir'] = INJI_SYSTEM_DIR . '/widgets/' . $widgetName . '/' . $widgetName . '.php';
            $paths['systemPath'] = INJI_SYSTEM_DIR . '/widgets/' . $widgetName . '.php';
        }
        return $paths;
    }

}
