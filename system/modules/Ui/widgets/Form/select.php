<div class="form-group">
    <label><?= $label; ?></label>
    <select class="form-control" name = '<?= $name; ?>'>
        <?php
        foreach ($options['values'] as $key=>$value){
            $selected = '';
            if(!empty($options['value']) && $key ==  $options['value']){
                $selected = ' selected="selected"';
            }
            echo "<option value ='{$key}'{$selected}>{$value}</option>";
        }
        ?>
    </select>
</div>