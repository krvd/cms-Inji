<table class ='table'>
    <?php
    $this->widget('Table/head', compact('table'));
    foreach ($table->rows as $row){
        $this->widget('Table/row', compact('row'));
    }
    $this->widget('Table/foot', compact('table'));
    ?>
</table>