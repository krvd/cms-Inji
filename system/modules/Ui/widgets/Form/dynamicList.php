<div class="dynamicList">
    <h3>
        <div class="pull-right">
            <?= !empty($options['modelPk']) ? '<a class="btn btn-primary btn-xs" onclick="inji.Ui.forms.addRowToList(this);">Добавить</a>' : ''; ?>
        </div>
        <?= $label; ?>
    </h3>
    <?php
    if (empty($options['modelPk'])) {
        echo '<h4 class=" text-muted">Чтобы добавить связи, сначала создайте объект</h4>';
        echo '<p class=" text-muted">Просто заполните доступные поля и нажмите кнопку внизу формы. После этого дополнительные поля разблокируются</p>';
    } else {
        ?>
        <div class="table-responsive">
            <table class ='table table-striped'>
                <thead>
                    <tr>
                        <?php
                        foreach ($options['cols'] as $colName => $col) {
                            echo "<th>{$col['label']}</th>";
                        }
                        ?>
                        <td>&nbsp;</td>
                    </tr>
                </thead>
                <tbody class="listBody">
                    <?php
                    $i = 0;
                    if (!empty($options['values'])) {
                        foreach ($options['values'] as $row) {
                            $i++;
                            echo '<tr>';
                            foreach ($options['cols'] as $colName => $col) {
                                echo '<td>';
                                $col['options']['noContainer'] = true;
                                $col['options']['value'] = $row[$colName];
                                $form->input($col['type'], $name . '[' . ($i) . '][' . $colName . ']', false, $col['options']);
                                echo '</td>';
                            }
                            echo '<td class="actionTd"><a class="btn btn-danger btn-xs" onclick="inji.Ui.forms.delRowFromList(this);"><i class="glyphicon glyphicon-remove"></i></a></td>';
                            echo '</tr>';
                        }
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <?php
                        foreach ($options['cols'] as $colName => $col) {
                            echo "<th>{$col['label']}</th>";
                        }
                        ?>
                        <td>&nbsp;</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="hidden sourceRow" data-counter='<?= $i; ?>'>
            <script>/*
             <tr>
    <?php
    foreach ($options['cols'] as $colName => $col) {
        echo '<td>';
        $col['options']['noContainer'] = true;
        $form->input($col['type'], $name . '[counterPlaceholder][' . $colName . ']', false, $col['options']);
        echo '</td>';
    }
    ?>
             <td class="actionTd"><a class="btn btn-danger btn-xs" onclick="inji.Ui.forms.delRowFromList(this);"><i class="glyphicon glyphicon-remove"></i></a></td>
             </tr>
             */</script>
        </div>
        <?php
    }
    ?>
</div>