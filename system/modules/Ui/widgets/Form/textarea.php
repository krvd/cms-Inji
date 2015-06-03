<div class="form-group">
    <label><?= $label; ?></label>
    <textarea class="form-control <?=!empty($options['class'])?$options['class']:'';?>" name = '<?= $name; ?>'><?= !empty($options['value']) ? $options['value'] : ''; ?></textarea>
</div>