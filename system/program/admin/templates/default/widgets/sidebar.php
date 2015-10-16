<div id="sidebar-wrapper">
  <ul class="sidebar-nav">
    <li class="sidebar-brand">
      <a href="/">
        Вернуться на сайт
      </a>
    </li>
    <li>
      <a href="/admin">Панель управления</a>
    </li>
    <?php
    $menu = Menu\Menu::get('sidebarMenu', 'code');
    foreach ($menu->items(['where' => ['parent_id', 0], 'order' => ['weight', 'asc']]) as $item) {
        echo "<li><a href = '{$item->href}'>{$item->name}</a>";
        $childItems = Menu\Item::getList(['where' => ['parent_id', $item->id]]);
        if ($childItems) {
            echo "<ul>";
            foreach ($childItems as $item) {
                echo "<li><a href = '{$item->href}'>{$item->name}</a>";
            }
            echo "</ul>";
        }
        echo "</li>";
    }
    ?>
  </ul>
</div>