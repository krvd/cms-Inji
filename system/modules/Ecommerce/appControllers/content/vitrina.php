<?php

foreach ($catalogs as $catalog) {
    $items = $this->ecommerce->getItems($catalog->catalog_id, 0, 12, 'ci_id', '', 'rand');
    if ($items) {
        $itemsCount = $this->ecommerce->getItemsCount($catalog->catalog_id, '');
        echo "<h2>{$catalog->catalog_name}</h2>";
        echo '<div class = "vitrin">';
        echo '<div class ="items">';

        foreach ($items as $item) {
            echo "<button class='pull-left item-icon'><img src = '{$item->options['itemImage']->file->file_path}?resize=120x120&resize_crop=q'/></button>";
            echo "<div class = 'hidden'>";
            $this->widget('item', $item);
            echo "</div>";
        }
        echo "</div>";
        echo '<div class ="clearfix"></div>';
        if ($itemsCount > 12) {
            echo "<hr />";

            echo "<div class = 'text-center'><big>Ещё <b>" . ($itemsCount - 12) . '</b> ' . $this->tools->getNumEnding($itemsCount - 12, ['Товар', 'Товара', 'Товаров']) . '</big></div>';
        }

        echo "<a class = 'vitrin-more' href = '/ecommerce/vitrina/{$catalog->catalog_id}'>Перейти в раздел</a>";
        echo "</div>";
    }

    //if ($catalog->items)
    //$this->widget('itemList', $catalog->items);
}
?>