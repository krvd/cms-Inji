<?php
if (!empty($this->contentData['category'])) {
    $catalogIds = array_values(array_filter(explode('/', $this->contentData['category']->tree_path)));
    $catalogIds[] = $this->contentData['category']->id;
} else {
    $catalogIds = [];
}
?>
<ul class="nav nav-list-main">
    <?php
    $catalogs = \Ecommerce\Category::getList(['order' => ['name', 'asc']]);
    foreach ($catalogs as $catalog) {
        if ($catalog->parent_id == 0) {
            showChildsCatalogs($catalog, $catalogs, $catalogIds);
        }
    }
    ?>
</ul>
<script>
    inji.onLoad(function () {
        $('ul.nav-left-ml').toggle();
        $('label.nav-toggle span').click(function () {
            $(this).parent().parent().children('ul.nav-left-ml').toggle(300);
            var cs = $(this).attr("class");
            if (cs == 'nav-toggle-icon glyphicon glyphicon-chevron-right') {
                $(this).removeClass('glyphicon-chevron-right').addClass('glyphicon-chevron-down');
            }
            if (cs == 'nav-toggle-icon glyphicon glyphicon-chevron-down') {
                $(this).removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-right');
            }
        });
    })
</script>
<?php

function showChildsCatalogs($parent, $catalogs, $catalogIds) {
    $isset = false;

    foreach ($catalogs as $catalog) {
        if ($catalog->parent_id == $parent->id) {
            if (!$isset) {
                $isset = true;

                echo "<li>
                    <label class='nav-toggle nav-header'>
                    <span class='nav-toggle-icon glyphicon ";
                if (in_array($parent->id, $catalogIds)) {
                    echo "glyphicon-chevron-down";
                } else {
                    echo "glyphicon-chevron-right";
                }
                echo "'></span> 
                    <a ";
                if (in_array($parent->id, $catalogIds)) {
                    echo ' style = "font-weight:bold;"';
                }
                echo "href='/ecommerce/itemList/{$parent->id}'>{$parent->name}</a></label>
                    <ul class='nav nav-list nav-left-ml' ";
                if (in_array($parent->id, $catalogIds)) {
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
        echo '<li><label class="nav-header"><a';
        if (in_array($parent->id, $catalogIds)) {
            echo ' style = "font-weight:bold;"';
        }
        echo ' href="/ecommerce/itemList/' . $parent->id . '">' . $parent->name . '</a></label></li>';
    }
}
