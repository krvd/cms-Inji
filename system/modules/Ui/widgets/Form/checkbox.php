<?php
echo empty($options['noContainer']) ? '<div class="checkbox">' : '';
echo $label !== false ? "<label>" : '';
?>
<input  type ="checkbox" name = '<?= $name; ?>' value = '1' <?= !empty($options['value']) ? 'checked' : ''; ?> />
<?php
echo $label !== false ? " {$label}</label>" : '';
echo!empty($options['helpText']) ? "<div class='help-block'>{$options['helpText']}</div>" : '';
echo empty($options['noContainer']) ? '</div>' : '';
?>