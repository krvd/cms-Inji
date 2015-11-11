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

namespace Ui;

class Tree extends \Object
{
    static function ul($objectRoot, $maxDeep = 0, $deep = 0)
    {
        $count = 0;
        ?>
        <ul class="nav nav-list-categorys" data-col='tree_path'>
          <li>
            <label class='nav-header'>
              <a href='#'><?= $objectRoot->name(); ?></a> 
            </label>
          </li>
          <?php
          $class = get_class($objectRoot);
          foreach ($class::getList(['where' => ['parent_id', $objectRoot->pk()]]) as $objectChild) {
              $count++;
              $count+=static::showLi($objectChild, $deep, $maxDeep);
          }
          ?>
        </ul>
        <?php
        return $count;
    }

    static function showLi($object, $deep = 0, $maxDeep = 0)
    {
        $count = 1;
        $isset = false;
        $class = get_class($object);
        if (!$maxDeep || $deep < $maxDeep) {
            foreach ($class::getList(['where' => ['parent_id', $object->pk()]]) as $objectChild) {
                if (!$isset) {
                    $isset = true;
                    ?>
                    <li id='<?= str_replace('\\', '_', get_class($object)) . ":{$object->pk()}"; ?>'>
                      <label class='nav-toggle nav-header'>
                        <span class='nav-toggle-icon glyphicon glyphicon-chevron-right'></span> 
                        <a href='#'> <?= $object->name(); ?></a> 
                      </label>
                      <ul class='nav nav-list nav-left-ml'>
                        <?php
                    }
                    $count+=static::showLi($objectChild, $deep + 1, $maxDeep);
                }
            }
            if ($isset) {
                ?>
              </ul>
            </li>
            <?php
        } else {
            ?>
            <li id='<?= str_replace('\\', '_', get_class($object)) . ":{$object->pk()}"; ?>'>
              <label class='nav-toggle nav-header'>
                <span class=' nav-toggle-icon glyphicon glyphicon-minus'></span> 
                <a href='#'> <?= $object->name(); ?></a> 
              </label>
            </li>
            <?php
        }
        return $count;
    }

}
