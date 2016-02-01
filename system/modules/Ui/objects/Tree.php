<?php
/**
 * Tree
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ui;

class Tree extends \Object
{
    public static function ul($objectRoot, $maxDeep = 0, $hrefFunc = null)
    {
        $count = 0;
        if (!$hrefFunc) {
            $hrefFunc = function($object) {
                echo "<a href='#'> {$object->name()}</a>";
            };
        }
        ?>
        <ul class="nav nav-list-categorys" data-col='tree_path'>
          <?php
          $class = get_class($objectRoot);
          $items = $class::getList(['where' => ['parent_id', $objectRoot->pk()]]);
          $count += count($items);
          foreach ($items as $objectChild) {
              $count+=static::showLi($objectChild, 1, $maxDeep, $hrefFunc);
          }
          ?>
        </ul>
        <?php
        return $count;
    }

    public static function showLi($object, $deep = 1, $maxDeep = 0, $hrefFunc = null)
    {
        $count = 0;
        $isset = false;
        $class = get_class($object);
        if (!$maxDeep || $deep < $maxDeep) {
            $items = $class::getList(['where' => ['parent_id', $object->pk()]]);
            $count += count($items);
            foreach ($items as $objectChild) {
                if (!$isset) {
                    $isset = true;
                    ?>
                    <li id='<?= str_replace('\\', '_', get_class($object)) . "-{$object->pk()}"; ?>'>
                      <label class='nav-toggle nav-header'>
                        <span class='nav-toggle-icon glyphicon glyphicon-chevron-right'></span> 
                        <?= $hrefFunc ? $hrefFunc($object) : "<a href='#'> {$object->name()}</a> "; ?>
                      </label>
                      <ul class='nav nav-list nav-left-ml'>
                        <?php
                    }
                    $count+=static::showLi($objectChild, $deep + 1, $maxDeep,$hrefFunc);
                }
            }
            if ($isset) {
                ?>
              </ul>
            </li>
            <?php
        } else {
            ?>
            <li id='<?= str_replace('\\', '_', get_class($object)) . "-{$object->pk()}"; ?>'>
              <label class='nav-toggle nav-header'>
                <span class=' nav-toggle-icon glyphicon glyphicon-minus'></span> 
                <?= $hrefFunc ? $hrefFunc($object) : "<a href='#'> {$object->name()}</a> "; ?>
              </label>
            </li>
            <?php
        }
        return $count;
    }

}
