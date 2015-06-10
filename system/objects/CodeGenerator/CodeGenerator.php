<?php

/**
 * Item name
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class CodeGenerator {

    static function genArray($data, $level = 0) {
        $return = '';
        if ($level == 0)
            $return = "[";
        foreach ($data as $key => $item) {
            $return .= "\n" . str_repeat(' ', ( $level * 4 + 4)) . "'{$key}' => ";
            if (!is_array($item))
                $return .= "'{$item}',";
            else {
                $return .= "[";
                $return .= rtrim(self::genArray($item, $level + 1), ',');
                $return .= "\n" . str_repeat(' ', ( $level * 4 + 4)) . "],";
            }
        }
        if ($level == 0)
            $return = rtrim($return, ',') . "\n];";

        return $return;
    }

}
