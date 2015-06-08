<?php if ($type != 'hidden') { ?>
    <div class="form-group">
        <label><?= $label; ?></label>
        <input  type ="<?= $type; ?>" placeholder="<?= !empty($options['placeholder']) ? $options['placeholder'] : ''; ?>" class="form-control" name = '<?= $name; ?>' value = '<?= !empty($options['value']) ? addcslashes($options['value'], "'") : ''; ?>' />
        <?= !empty($options['helpText']) ? "<div class='help-block'>{$options['helpText']}</div>" : ''; ?>
    </div>
    <?php
} else {
    ?>
    <input type ="hidden" name = '<?= $name; ?>' value = '<?= !empty($options['value']) ? addcslashes($options['value'], "'") : ''; ?>' />
    <?php
}
?>