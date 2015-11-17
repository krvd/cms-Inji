<form <?= $form->id ? 'id="' . $form->id . '"' : ''; ?> action ="<?= $form->action; ?>" method ="<?= $form->method; ?>"  enctype="multipart/form-data"
<?php
foreach ($options as $attribute => $value) {
    echo " {$attribute} = '{$value}' ";
}
?>>
  <?= !empty($header) ? "<h1>{$header}</h1>" : ''; ?>
    