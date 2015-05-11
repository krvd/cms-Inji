<div id="sidebar-wrapper">
    <ul class="sidebar-nav">
        <li class="sidebar-brand">
            <a href="/">
                CMS Inji
            </a>
        </li>
        <li>
            <a href="/admin">Панель управления</a>
        </li>
        <?php
            $menu = Menu\Menu::get('sidebarMenu','code');
            foreach ($menu->items as $item){
                echo "<li><a href = '{$item->href}'>{$item->name}</a></li>";
            }
        ?>
    </ul>
</div>