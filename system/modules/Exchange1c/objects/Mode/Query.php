<?php

/**
 * Item name
 *
 * Info
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Exchange1c\Mode;

class Query extends \Exchange1c\Mode {

    function process() {
        header("Content-Type: text/xml");
        header("Expires: Thu, 19 Feb 1998 13:24:18 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Cache-Control: post-check=0,pre-check=0");
        header("Cache-Control: max-age=0");
        header("Pragma: no-cache");
        $xml = new \DOMDocument('1.0', 'windows-1251');

        $xml->formatOutput = true;

        $root = $xml->createElement('КоммерческаяИнформация');
        $root->setAttribute("ВерсияСхемы", "2.03");
        $root->setAttribute("ДатаФормирования", date('Y-m-d'));
        $root = $xml->appendChild($root);
        echo $xml->saveXML();

        $this->end();
    }

}
