<?php

if (!empty($params[0])) {
    $code = $params[0];
}
if (empty($code)) {
    $code = 'main';
}
$menu = \Menu\Menu::get($code, 'code');
if ($menu)
    foreach ($menu->items(['order' => ['weight', 'ASC']]) as $item) {
        $href = $item->href;
        if ($item->type == 'materialCategory') {
            $category = \Materials\Category::get($item->aditional);
            $href = $category->alias ? "/materials/{$category->alias}" : "/materials/category/{$category->id}";
        }
        if (urldecode($_SERVER['REQUEST_URI']) == $href)
            $active = ' class = "active" ';
        else
            $active = '';
        echo "<li {$active}><a href = '{$href}'>{$item->name}</a></li>";
    }
