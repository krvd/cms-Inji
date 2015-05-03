<h1>
    <?= $table->name; ?>
    <div class ='pull-right'>
        <?php
        foreach ($table->buttons as $button) {
            $html = '<a class = "btn btn-primary"';
            if (!empty($button['href'])) {
                $html .= " href = '{$button['href']}'";
            }
            if (!empty($button['onclick'])) {
                $html .= " onclick = '{$button['onclick']}'";
            }
            $html .= ">{$button['text']}</a> ";
        }
        echo $html;
        ?>
    </div>
</h1>
<table class ='table'>
    <?php
    $this->widget('Ui\Table/head', compact('table'));
    foreach ($table->rows as $row) {
        $this->widget('Ui\Table/row', compact('row'));
    }
    $this->widget('Ui\Table/foot', compact('table'));
    ?>
</table>