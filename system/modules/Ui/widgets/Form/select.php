<?php
$aditionalInputs = [];
$showedInput = false;
$optionsHtml = '';
foreach ($options['values'] as $key => $value) {
    $selected = '';

    $primaryValue = isset($options['value']) ? $options['value'] : null;
    $primaryValue = is_array($primaryValue) ? $primaryValue['primary'] : $primaryValue;
    if ($key == $primaryValue || (!empty($form->userDataTree[$name]) && $form->userDataTree[$name] == $key)) {
        $selected = ' selected="selected"';
    }
    if (is_array($value)) {
        $aditionalInputs[] = $value['input'];
        if ($selected) {
            $showedInput = count($aditionalInputs) - 1;
            $aditionValue = $options['value']['aditional'];
        }
        $optionsHtml .= "<option data-aditionalInput='" . ( count($aditionalInputs) - 1) . "' value ='{$key}'{$selected}>{$value['text']}</option>";
    } else {
        $optionsHtml .= "<option value ='{$key}'{$selected}>{$value}</option>";
    }
}
?>
<?= empty($options['noContainer']) ? '<div class="form-group">' : ''; ?>
<?= $label !== false ? "<label>{$label}</label>" : ''; ?>
<select <?= ($showedInput !== false) ? 'data-aditionalEnabled="1"' : ''; ?> <?= !empty($options['disabled']) ? 'disabled="disabled"' : ''; ?> onchange="inji.Ui.forms.checkAditionals(this);" class="form-control <?= !empty($options['class']) ? $options['class'] : ''; ?>" name = '<?= $name; ?><?= ($showedInput !== false) ? '[primary]' : ''; ?>'>
    <?= $optionsHtml; ?>
</select>
<?php
foreach ($aditionalInputs as $key => $input) {
    $input['options']['noContainer'] = true;

    if ($key !== $showedInput) {
        $input['options']['disabled'] = true;
        $input['options']['class'] = !empty($input['options']['class']) ? $input['options']['class'] . ' hidden' : 'hidden';
    } else {
        $input['options']['value'] = $aditionValue;
    }
    $form->input($input['type'], $name . '[aditional]', false, $input['options']);
}
?>
<?= empty($options['noContainer']) ? '</div>' : ''; ?>