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
                return "<a href='#'> {$object->name()}</a>";
            };
        }
        ?>
        <ul class="treeview" data-col='tree_path'>
          <?php
          if (is_string($objectRoot)) {
              $items = $objectRoot::getList(['where' => ['parent_id', 0]]);
          } else {
              $class = get_class($objectRoot);
              $items = $class::getList(['where' => ['parent_id', $objectRoot->pk()]]);
          }
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
            $item = $hrefFunc ? $hrefFunc($object) : "<a href='#'> {$object->name()}</a> ";
            if (is_array($item)) {
                $class = $item['class'];
                $item = $item['text'];
            } else {
                $class = '';
            }
            foreach ($items as $objectChild) {
                if (!$isset) {
                    $isset = true;
                    ?>
                    <li id='<?= str_replace('\\', '_', get_class($object)) . "-{$object->pk()}"; ?>' class="<?= $class; ?>">
                      <?= $item; ?>
                      <ul>
                        <?php
                    }
                    $count+=static::showLi($objectChild, $deep + 1, $maxDeep, $hrefFunc);
                }
            }
            if ($isset) {
                ?>
              </ul>
            </li>
            <?php
        } else {
            ?>
            <li id='<?= str_replace('\\', '_', get_class($object)) . "-{$object->pk()}"; ?>' class="<?= $class; ?>">
              <?= $item; ?>
            </li>
            <?php
        }
        return $count;
    }

}
