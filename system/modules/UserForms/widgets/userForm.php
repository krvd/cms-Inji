<?php
if (!empty($params[0])) {
    $form_id = $params[0];
}
if (empty($form_id)) {
    echo('form not found');
    return;
}
$form = \UserForms\Form::get((int) $form_id);
if (!$form) {
    echo('form not found');
    return;
}
?>
<form method = "POST" action = "">
  <?php
  if ($form->description) {
      echo "<p class = 'text-center'>{$form->description}</p>";
  }
  foreach ($form->inputs(['order' => ['weight']]) as $input) {
      switch ($input->type) {
          case 'text':
              ?>
              <div class ='form-group'>
                <label><?= $input->label; ?></label>
                <input class ='form-control' type ='text' name ='UserForms[<?= (int) $form_id; ?>][input<?= $input->id; ?>]' <?= $input->required ? 'required' : ''; ?> />
              </div>
              <?php
              break;
          case 'textarea':
              ?>
              <div class ='form-group'>
                <label><?= $input->label; ?></label>
                <textarea class ='form-control' name ='UserForms[<?= (int) $form_id; ?>][input<?= $input->id; ?>]' <?= $input->required ? 'required' : ''; ?> /></textarea>
              </div>
              <?php
              break;
      }
  }
  ?>
  <button class = 'btn btn-success btn-block'>Отправить</button>
</form>