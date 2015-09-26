<thead>
    <tr>
        <?php
        foreach ($table->cols as $col) {
            echo "<th>{$col}</th>";
        }
        ?>
    </tr>
</thead>