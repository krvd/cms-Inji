<?php
echo empty($options['noContainer']) ? '<div class="form-group">' : '';
echo $label !== false ? "<label>{$label}" . (!empty($options['required']) ? ' <span class="required-star">*</span>' : '') . "</label>" : '';
?>
<input <?= !empty($options['required']) ? 'required' : ''; ?> 
  type ="password" 
  placeholder="<?= !empty($options['placeholder']) ? $options['placeholder'] : ''; ?>" 
  class="form-control" 
  name = '<?= $name; ?>' 
  />
<?php
echo!empty($options['helpText']) ? "<div class='help-block'>{$options['helpText']}</div>" : '';
echo empty($options['noContainer']) ? '</div>' : '';
