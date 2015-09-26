<div class="form-group">
    <label><?= $label; ?></label>
    <textarea <?= !empty($options['required']) ? 'required' : ''; ?> class="form-control <?= !empty($options['class']) ? $options['class'] : ''; ?>" name = '<?= $name; ?>'><?= !empty($options['value']) ? $options['value'] : (!empty($form->userDataTree[$name]) ? addcslashes($form->userDataTree[$name], "'") : ''); ?></textarea>
</div>