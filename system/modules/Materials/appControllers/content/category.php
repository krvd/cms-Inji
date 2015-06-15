<div class ="row">
    <?php
    $i = 0;
    foreach ($materials as $material) {
        ?>
        <div class = "col-sm-6">
            <a href ="/<?= $material->chpu; ?>"><h1><?= $material->name; ?></h1></a>
            <div><?= $material->preview; ?></div>
            <a href ="/<?= $material->chpu; ?>" class = 'pull-right' style = 'color: #5E5B55;font-weight: bold;'>Читать далее <i class ='glyphicon glyphicon-forward'></i> </a>
            <div class="clearfix"></div>
        </div>
        <?php
        if (!( ++$i % 2)) {
            echo '</div><hr /><div class ="row">';
        }
    }
    ?>
</div>
<?php
$pages->draw();
?>