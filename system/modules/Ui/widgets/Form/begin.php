<form action ="<?= $form->action; ?>" method ="<?= $form->method; ?>"             
<?php
foreach ($options as $attribute => $value) {
    echo " {$attribute} = '{$value}' ";
}
?>>
    <?= !empty($header) ? "<h1>{$header}</h1>" : ''; ?>
    