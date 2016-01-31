<?php

$html = '';
foreach ($buttons as $button) {
    $html .= '<a class = "btn btn-primary btn-sm"';
    if (!empty($button['href'])) {
        $html .= " href = '{$button['href']}'";
    }
    if (!empty($button['onclick'])) {
        $html .= " onclick = '{$button['onclick']}'";
    }
    $html .= ">{$button['text']}</a> ";
}
echo $html;
?>