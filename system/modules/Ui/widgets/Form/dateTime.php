<?php
App::$cur->libs->loadLib('JqueryUi');
echo empty($options['noContainer']) ? '<div class="form-group">' : '';
echo $label !== false ? "<label>{$label}</label>" : '';
$uid = Tools::randomString();
?>
<input data-dateui="<?= $uid; ?>" type ="text" placeholder="<?= !empty($options['placeholder']) ? $options['placeholder'] : ''; ?>" class=" <?= !empty($options['class']) ? $options['class'] : 'form-control'; ?>" name = '<?= $name; ?>' value = '<?= (!empty($form->userDataTree[$name]) ? addcslashes($form->userDataTree[$name], "'") : (!empty($options['value']) ? addcslashes($options['value'], "'") : '')); ?>' />
<?php
echo!empty($options['helpText']) ? "<div class='help-block'>{$options['helpText']}</div>" : '';
echo empty($options['noContainer']) ? '</div>' : '';
?>
<script>
    inji.onLoad(function () {
      $("[data-dateui='<?= $uid; ?>']").datetimepicker({
        changeMonth: true,
        changeYear: true,
        numberOfMonths: 1,
        dateFormat: 'yy-mm-dd',
        yearRange: "c-70:c+10",
        timeFormat: 'HH:mm:ss',
        beforeShow: function () {
          setTimeout(function () {
            $('.ui-datepicker').css('z-index', 99999999999999);
          }, 500);
        }
      });
    })
</script>