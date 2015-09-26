<?php
$catalogIds = [];
?>
<ul class="nav nav-list-main">
    <?php
    $catalogs = Catalog::get_list(['order' => ['catalog_name', 'asc']]);
    foreach ($catalogs as $catalog) {
        if ($catalog->catalog_parent_id == 0) {
            showChildsCatalogs($catalog, $catalogs, $catalogIds);
        }
    }
    ?>
</ul>
<?php
function showChildsCatalogs($parent, $catalogs, $catalogIds)
{
    $isset = false;

    foreach ($catalogs as $catalog) {
        if ($catalog->catalog_parent_id == $parent->catalog_id) {
            if (!$isset) {
                $isset = true;

                echo "<li>
                    <label class='nav-toggle nav-header'";
                if (in_array($parent->catalog_id, $catalogIds)) {
                    echo ' style = "font-weight:bold;"';
                }
                echo ">
                    <span class='nav-toggle-icon glyphicon ";
                if (in_array($parent->catalog_id, $catalogIds)) {
                    echo "glyphicon-chevron-down";
                } else {
                    echo "glyphicon-chevron-right";
                }
                $itemsCount = Inji::app()->ecommerce->getItemsCount(['parent' => $parent->catalog_id]);
                echo "'></span> 
                    <a href='/ecommerce/itemList/{$parent->catalog_id}'>{$parent->catalog_name}</a> {$itemsCount}</label>
                    <ul class='nav nav-list nav-left-ml' ";
                if (in_array($parent->catalog_id, $catalogIds)) {
                    echo "style='display: none;'";
                }
                echo ">";
            }
            showChildsCatalogs($catalog, $catalogs, $catalogIds);
        }
    }
    if ($isset) {
        echo '</ul>
                    </li>';
    } else {
        echo '<li><label class="nav-header"';
        if (in_array($parent->catalog_id, $catalogIds)) {
            echo ' style = "font-weight:bold;"';
        }
        $itemsCount = Inji::app()->ecommerce->getItemsCount(['parent' => $parent->catalog_id]);
        echo '><a href="/ecommerce/itemList/' . $parent->catalog_id . '">' . $parent->catalog_name . '</a> ' . $itemsCount . '</label></li>';
    }
}
