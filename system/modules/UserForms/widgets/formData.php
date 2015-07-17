<?php

$inputs = json_decode($params[0]->fr_data, true);
foreach ($params[0]->form->inputs as $input) {
    if (isset($inputs['input' . $input->fi_id])) {
        echo "{$input->fi_label}: ".htmlspecialchars($inputs['input' . $input->fi_id])."<br />";
    }
}
