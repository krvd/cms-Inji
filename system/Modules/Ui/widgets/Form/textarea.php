<div class="form-group">
    <label><?= $label; ?></label>
    <textarea class="form-control" name = '<?= $name; ?>'><?= !empty($options['value']) ? $options['value'] : ''; ?></textarea>
</div>