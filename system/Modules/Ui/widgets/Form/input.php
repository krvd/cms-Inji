<?php if ($type != 'hidden') { ?>
    <div class="form-group">
        <label><?= $label; ?></label>
        <input  type ="<?=$type;?>" class="form-control" name = '<?= $name; ?>' value = '<?= !empty($options['value']) ? addcslashes($options['value'], "'") : ''; ?>' />
    </div>
    <?php
} else {
    ?>
    <input type ="hidden" name = '<?= $name; ?>' value = '<?= !empty($options['value']) ? addcslashes($options['value'], "'") : ''; ?>' />
    <?php
}
?>