<?php
echo empty($options['noContainer']) ? '<div class="form-group">' : '';
echo $label !== false ? "<label>{$label}</label>" : '';
$value = !empty($options['value']) ? addcslashes($options['value'], "'") : (!empty($form->userDataTree[$name]) ? addcslashes($form->userDataTree[$name], "'") : '');
?>
<input <?= !empty($options['required']) ? 'required' : ''; ?> 
  type ="text" 
  autocomplete="off"
  placeholder="<?= !empty($options['placeholder']) ? $options['placeholder'] : ''; ?>" 
  class="form-control" 
  name = 'query-<?= $name; ?>' 
  value = '<?=!empty($options['values'][$value])?$options['values'][$value]:'';?>'
  />
<input 
  type="hidden" 
  name = '<?= $name; ?>'
  value = '<?= $value; ?>'
  />
<div class="form-search-results"></div>
<?php
echo!empty($options['helpText']) ? "<div class='help-block'>{$options['helpText']}</div>" : '';
echo empty($options['noContainer']) ? '</div>' : '';
?>