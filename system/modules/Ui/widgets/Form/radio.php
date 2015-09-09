<?php
echo empty($options['noContainer']) ? '<div class="radio">' : '';
echo $label !== false ? "<label>" : '';
?>
<input <?= !empty($options['disabled']) ? 'disabled="disabled"' : ''; ?> type ="radio" name = '<?= $name; ?>' value = '<?= $options['value']; ?>' <?= !empty($options['checked']) ? 'checked' : ''; ?> />
<?php
echo $label !== false ? " {$label}</label>" : '';
echo!empty($options['helpText']) ? "<div class='help-block'>{$options['helpText']}</div>" : '';
echo empty($options['noContainer']) ? '</div>' : '';
?>