echo 1;
<div class="row ">
    <div class="col-md-3 col-xs-3 col-sm-3 col-lg-3">
        <ul class="nav ">
            <?php
            $materials = $this->materials->get_list(2);
            foreach ($materials as $curpage) {
                echo '<li class="nav-header">' . $curpage['material_name'] . '</li>';
                $childs = $this->materials->get_list($curpage['material_id']);
                if ($childs) {
                    foreach ($childs as $child) {
                        echo '<li><a href = "' . $child['material_chpu'] . '">' . $child['material_name'] . '</a></li>';
                    }
                }
            }
            ?>
        </ul>
    </div>

    <div class="col-md-9 col-xs-9 col-sm-9 col-lg-9 ">
        <!-- Заголовок страницы -->
        <h1><?php echo $material['material_name']; ?></h1>
        <?php echo $material['material_text']; ?>
    </div>

</div>
