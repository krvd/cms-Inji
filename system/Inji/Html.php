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

/**
 * Description of Html
 *
 * @author inji
 */
class Html {

    static function el($tag, $attributes = [], $body = '', $noCloseTag = false) {
        $html = "<{$tag}";
        if ($attributes && is_array($attributes)) {
            foreach ($attributes as $key => $value) {
                $html .=" {$key} = '";
                if (!is_array($value)) {
                    $html .= addcslashes($value, "'");
                } else {
                    $html .= json_encode($value);
                }
                $html .= "'";
            }
        }
        if ($noCloseTag === null) {
            $html .= ' />';
        } elseif ($noCloseTag === false) {
            $html .= ">{$body}</{$tag}>";
        } else {
            $html .= ">{$body}";
        }
        return $html;
    }

}
