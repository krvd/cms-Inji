<?= Ui\FastEdit::block($material, 'text', null, true); ?>
<?php
$nexts = json_decode($material->nexts, true);

if ($nexts) {
    echo '<p style = "text-align:center">';
    foreach ($nexts as $next) {
        $nextpage = Material::get($next['id']);
        $href = $nextpage->chpu;
        if ($href == '') {
            $href = '/';
        }
        echo "<a href = '{$href}' class = 'nextbtn'>{$next['name']}</a>";
    }
    echo '</p>';
}