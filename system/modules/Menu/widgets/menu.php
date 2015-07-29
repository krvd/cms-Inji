<?php

$menu = \Menu\Menu::get($code, 'code');
foreach ($menu->items as $item) {
    $href = $item->href;
    if ($item->type == 'materialCategory') {
        $category = \Materials\Category::get($item->aditional);
        $href = $category->alias ? "/materials/{$category->alias}" : "/materials/category/{$category->id}";
    }
    echo "<li><a href = '{$href}'>{$item->name}</a></li>";
}
