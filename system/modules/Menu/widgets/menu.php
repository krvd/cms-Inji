<?php

$menu = \Menu\Menu::get($code, 'code');
foreach ($menu->items as $item) {
    echo "<li><a href = '{$item->href}'>{$item->name}</a></li>";
}
