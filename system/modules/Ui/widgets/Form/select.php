<?= empty($options['noContainer']) ? '<div class="form-group">' : ''; ?>
<?= $label !== false ? "<label>{$label}</label>" : ''; ?>
<select <?= !empty($options['disabled']) ? 'disabled="disabled"' : ''; ?> onchange="inji.Ui.forms.checkAditionals(this);" class="form-control <?= !empty($options['class']) ? $options['class'] : ''; ?>" name = '<?= $name; ?>'>
    <?php
    $aditionalInputs = [];
    $showedInput = false;
    foreach ($options['values'] as $key => $value) {
        $selected = '';
        if (!empty($options['value']) && $key == $options['value']) {
            $selected = ' selected="selected"';
        }
        if (is_array($value)) {
            $aditionalInputs[] = $value['input'];
            if ($selected) {
                $showedInput = count($aditionalInputs) - 1;
            }
            echo "<option data-aditionalInput='" . ( count($aditionalInputs) - 1) . "' value ='{$key}'{$selected}>{$value['text']}</option>";
        } else {
            echo "<option value ='{$key}'{$selected}>{$value}</option>";
        }
    }
    ?>
</select>
<?php
foreach ($aditionalInputs as $key => $input) {
    $input['options']['noContainer'] = true;
    
    if ($key !== $showedInput) {
        $input['options']['disabled'] = true;
        $input['options']['class'] = !empty($input['options']['class']) ? $input['options']['class'] . ' hidden' : 'hidden';
    }
    $form->input($input['type'], $name . '[aditional]', false, $input['options']);
}
?>
<?= empty($options['noContainer']) ? '</div>' : ''; ?>