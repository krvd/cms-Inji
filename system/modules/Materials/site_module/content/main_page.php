<div class = 'fastEdit' data-model='Material' data-col='material_text' data-key='<?= $material->material_id; ?>'>
    <?php
    $this->parse_proc($material->material_text);
    ?>
</div>
<?php
$nexts = json_decode($material->material_nexts, true);

if ($nexts) {
    echo '<p style = "text-align:center">';
    foreach ($nexts as $next) {
        $nextpage = Material::get($next['material_id']);
        $href = $nextpage->material_chpu;
        if ($href == '') {
            $href = '/';
        }
        echo "<a href = '{$href}' class = 'nextbtn'>{$next['name']}</a>";
    }
    echo '</p>';
}