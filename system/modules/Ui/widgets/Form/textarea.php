<div class="form-group">
    <label><?= $label; ?></label>
    <textarea class="form-control <?=!empty($options['class'])?$options['class']:'';?>" name = '<?= $name; ?>'><?= !empty($options['value']) ? $options['value'] : (!empty($form->userDataTree[$name]) ? addcslashes($form->userDataTree[$name], "'"):''); ?></textarea>
</div>