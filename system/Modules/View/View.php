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
        if (!empty(Inji::app()->Config->app['site']['name'])) {
            $this->title = Inji::app()->Config->site['site']['name'];
        }
        if (!empty($this->config['current'])) {
            $this->template['name'] = $this->config['current'];
        }

        $this->templatesPath = Inji::app()->curApp['path'] . "/templates/";

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
            'module' => Inji::app()->curModule,
            'contentPath' => Inji::app()->curController->path . '/content',
            'content' => Inji::app()->curController->method
        );
    }

    function getConfig($templateName) {
        return Inji::app()->config->custom($this->templatesPath . "/{$templateName}/config.php");
    }

    function page($params = []) {
        $data = $this->paramsParse($params);

        if (file_exists($data['path'])) {
            $source = file_get_contents($data['path']);
            $this->parse_proc($source);
        } else {
            $this->content();
        }
    }

    function paramsParse($params) {
        $data = $this->tmp_data;
        // set template
        if (!empty($params['template'])) {
            if (file_exists($this->template['path'] . "/{$params['template']}.html")) {
                $data['path'] = $this->template['path'];
                $data['file'] = $params['template'] . '.html';
            } elseif ($template = $this->getConfig($params['template'])) {
                $this->template = $template;
                $data['path'] = $this->template['path'] = $this->templatesPath . '/' . $this->template['name'];
                $data['name'] = $this->template['name'];
                $data['file'] = $this->template['file'];
            }
        }
        //set module
        if (!empty($params['module'])) {
            $data['module'] = Inji::app()->$params['module'];
        }
        //set content
        if (!empty($params['content'])) {
            $paths = [
                'template' => $this->template['path'] . "/modules/{$data['module']->moduleName}",
                'customContent' => Inji::app()->curApp['path'] . '/modules/' . $data['module']->moduleName . '/Controllers/content',
                'controlelrContent' => Inji::app()->curController->path . '/content'
            ];
            foreach ($paths as $type => $path) {
                if (file_exists($path . '/' . $params['content'] . '.php')) {
                    $data['contentPath'] = $path;
                    $data['content'] = $params['content'];
                }
            }
        }
        if (!empty($params['data'])) {
            $this->contentData = array_merge($this->contentData, $params['data']);
        }
        return $data;
    }

    function getParentConfig() {
        return include Inji::app()->app['parent']['path'] . "/templates/{$this->modConf['site']['current']}/config.php";
    }

    private function parse_str($source) {
        if (!$source)
            return array();

        preg_match_all("|{(.*)}|", $source, $result);
        return $result[1];
    }

    function parse_proc($source) {
        $links = $this->parse_str($source);
        foreach ($links as $link) {
            $link1 = $link;
            $link = explode(':', $link);
            if (!empty($this->cals_methods[$link[0]])) {

                $pos = strpos($source, $link1) - 1;
                echo substr($source, 0, $pos);
                $source = substr($source, ( $pos + strlen($link1) + 2));

                $name = $this->cals_methods[$link[0]]['method_name'];
                array_shift($link);
                $this->parsing = true;
                call_user_func_array(array($this, $name), $link);
                $this->parsing = false;
            } elseif ($link[0] == 'name') {
                $pos = strpos($source, $link1) - 1;
                echo substr($source, 0, $pos);
                $source = substr($source, ( $pos + strlen($link1) + 2));
                echo $this->template['name'];
            } elseif ($link[0] == 'TEMPLATE_PATH') {
                $pos = strpos($source, $link1) - 1;
                echo substr($source, 0, $pos);
                $source = substr($source, ( $pos + strlen($link1) + 2));
                echo Inji::app()->app['templates_path'] . '/' . $this->template['name'];
            } elseif ($link[0] == 'TITLE') {
                $pos = strpos($source, $link1) - 1;
                echo substr($source, 0, $pos);
                $source = substr($source, ( $pos + strlen($link1) + 2));
                echo $this->title;
            }
        }
        echo $source;
    }

    function content($params = []) {
        $this->current_function = 'CONTENT';
        if (Inji::app()->msg && empty($this->template['noSysMesAutoShow'])) {
            Inji::app()->msg->show(true);
        }

        $_params = $this->paramsParse($params);
        extract($this->contentData);
        include $_params['contentPath'] . '/' . $_params['content'] . '.php';
    }

    function parentContent() {
        $this->current_function = 'CONTENT';
        Inji::app()->msg->show(true);
        $inji_data = func_get_args();
        $inji_data = $this->parse_args($inji_data);
        $inji_path = Inji::app()->controller['dir'] . '/content';
        if (!empty($inji_data['data']) && is_array($inji_data['data']))
            extract($inji_data['data']);
        if (!empty($this->contentData) && is_array($this->contentData))
            extract($this->contentData);

        include $inji_path . "/{$this->tmp_data['content']}.php";
    }

    function head() {
        $this->current_function = 'HEAD';
        $config = $this->modConf[$this->app['type']];
        $current = $this->tmp_data['name'];

        if (isset($config['favicon']) && file_exists(Inji::app()->app['path'] . "/templates/{$current}/images/{$config['favicon']}"))
            echo "<link rel='shortcut icon' href='" . Inji::app()->app['templates_path'] . "/{$current}/images/{$config['favicon']}' />";
        elseif (file_exists(Inji::app()->app['path'] . '/static/images/favicon.ico'))
            echo '<link rel="shortcut icon" href="/static/images/favicon.ico" />';

        echo "<title>{$this->title}</title>\n";
        if (!empty(Inji::app()->Config->site['site']['keywords'])) {
            echo "\n        <meta name='keywords' content='" . Inji::app()->Config->site['site']['keywords'] . "' />";
        }
        if (!empty(Inji::app()->Config->site['site']['description'])) {
            echo "\n        <meta name='description' content='" . Inji::app()->Config->site['site']['description'] . "' />";
        }
        if (!empty(Inji::app()->Config->site['site']['metatags'])) {
            foreach (Inji::app()->Config->site['site']['metatags'] as $meta)
                echo "\n        <meta name='{$meta['name']}' content='{$meta['content']}' />";
        }



        if (!empty($this->libAssets['css'])) {
            foreach ($this->libAssets['css'] as $css) {
                if (strpos($css, '//') !== false)
                    $href = $css;
                else
                    $href = $css;
                echo "\n        <link href='{$href}' rel='stylesheet' type='text/css' />";
            }
        }
        if (!empty($this->template['css'])) {
            foreach ($this->template['css'] as $css) {
                if (strpos($css, '://') !== false)
                    $href = $css;
                else
                    $href = Inji::app()->app['templates_path'] . "/{$current}/css/{$css}";
                echo "\n        <link href='{$href}' rel='stylesheet' type='text/css' />";
            }
        }
        if (!empty($this->dynAssets['css'])) {
            foreach ($this->dynAssets['css'] as $css) {
                if (strpos($css, '//') !== false)
                    $href = $css;
                else
                    $href = $css;
                echo "\n        <link href='{$href}' rel='stylesheet' type='text/css' />";
            }
        }
        if (!empty($this->libAssets['js'])) {
            foreach ($this->libAssets['js'] as $js) {
                if (is_string($js)) {
                    $href = $js;
                } elseif (strpos($js['file'], '//') !== false)
                    $href = $js['file'];
                elseif ($js['template'])
                    $href = Inji::app()->app['templates_path'] . "/{$current}/js/{$js['file']}";
                else
                    $href = $js['file'];
                echo "\n        <script src='{$href}'></script>";
            }
        }
        if (!empty($this->template['js'])) {
            foreach ($this->template['js'] as $js) {
                if (strpos($js, '://') !== false)
                    $href = $js;
                else
                    $href = Inji::app()->app['templates_path'] . "/{$current}/js/{$js}";
                echo "\n        <script src='{$href}'></script>";
            }
        }
        if (!empty($this->dynAssets['js'])) {
            foreach ($this->dynAssets['js'] as $js) {
                if (is_string($js)) {
                    $href = $js;
                } elseif (strpos($js['file'], '//') !== false)
                    $href = $js['file'];
                elseif ($js['template'])
                    $href = Inji::app()->app['templates_path'] . "/{$current}/js/{$js['file']}";
                else
                    $href = $js['file'];
                echo "\n        <script src='{$href}'></script>";
            }
        }
    }

    function widget($path) {
        $this->current_function = 'WIDGET';
        if (is_array($path))
            $path = $path[1];
        $params = func_get_args();
        array_shift($params);
        $lineParams = '';
        if ($this->parsing && $params) {
            $paramArray = false;
            foreach ($params as $param) {
                if (is_array($param) || is_object($param)) {
                    $paramArray = true;
                }
            }
            if (!$paramArray)
                $lineParams = ':' . implode(':', $params);
        }
        $this->parsing = false;

        echo "<!--start:{WIDGET:{$path}{$lineParams}}-->\n";
        if (file_exists(Inji::app()->app['path'] . "/templates/{$this->template['name']}/widgets/{$path}.php"))
            include( Inji::app()->app['path'] . "/templates/{$this->template['name']}/widgets/{$path}.php" );
        elseif (file_exists(Inji::app()->app['path'] . "/widgets/{$path}.php"))
            include( Inji::app()->app['path'] . "/widgets/{$path}.php" );
        elseif (file_exists(Inji::app()->app['path'] . "/widgets/{$path}/{$path}.php"))
            include( Inji::app()->app['path'] . "/widgets/{$path}/{$path}.php" );
        echo "<!--end:{WIDGET:{$path}{$lineParams}}-->\n";
    }

    function moduleWidget($module, $widget) {
        $this->current_function = 'MODULEWIDGET';
        $params = func_get_args();
        if (is_array($module)) {
            $module = $module[1];
            $widget = $module[2];
            array_shift($params);
        } else {
            array_slice($params, 2);
        }


        $lineParams = '';
        if ($this->parsing && $params) {
            $paramArray = false;
            foreach ($params as $param) {
                if (is_array($param) || is_object($param)) {
                    $paramArray = true;
                }
            }
            if (!$paramArray)
                $lineParams = ':' . implode(':', $params);
        }
        $this->parsing = false;
        echo "<!--start:{MODULEWIDGET:{$module}:{$widget}{$lineParams}}-->\n";
        if (file_exists(INJI_SYSTEM_DIR . '/modules/' . $module . '/widgets/' . $widget . '.php'))
            include( INJI_SYSTEM_DIR . '/modules/' . $module . '/widgets/' . $widget . '.php' );
        echo "<!--end:{MODULEWIDGET:{$module}:{$widget}{$lineParams}}-->\n";
    }

    function template() {
        $this->current_function = 'TEMPLATE';

        $file_path = Inji::app()->app['path'] . "/module/{$controller}/content/template/{$page}.inji";

        if (!isset($this->load_files_temlate[$file_path])) {
            if (file_exists($file_path) && $file = fopen($file_path, "r")) {
                $this->load_files_temlate[$file_path] = fread($file, filesize($file_path));
                fclose($file);
            } else
                $this->load_files_temlate[$file_path] = false;
        }

        $source = $this->load_files_temlate[$file_path];

        if ($source !== false && !empty($source))
            $this->parse_proc($source);
    }

    function timegen() {
        $this->current_function = 'TIMEGEN';
        echo round(( microtime(true) - INJI_TIME_START), 4);
    }

    function assetFile($type, $file, $template = true) {
        $this->dynAssets[$type][] = compact('file', 'template');
    }

    function customAsset($type, $href, $lib = false) {
        if (!$lib) {
            $this->dynAssets[$type][] = $href;
        } else {
            $this->libAssets[$type][] = $href;
        }
    }

    function set_title($title, $add = true) {
        if ($add && !empty(Inji::app()->Config->site['site']['name'])) {
            $this->title = $title . ' - ' . Inji::app()->Config->site['site']['name'];
        } else {
            $this->title = $title;
        }
    }

}

?>
