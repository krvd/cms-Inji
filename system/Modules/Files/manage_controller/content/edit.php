<div class = 'content-box'>
    <h1 class = 'content-head'>редактирование шаблона</h1>
    <div class = 'content-body'>
        <div class = 'content-body-container'>
<form action = '' method = 'POST'>
    название<br />
    <input type = 'text' name = 'template_name' value = '<?php echo $template['template_name'];?>' /><br />
    js<br />
    <?php
        foreach( $template['js'] as $js )
            echo "<input type = 'text' name = 'js[]' value = '{$js}' /><br />";

        echo "<input type = 'text' name = 'js[]' /><br />";

    ?>
    css<br />
    <?php
        foreach( $template['css'] as $css )
            echo "<input type = 'text' name = 'css[]' value = '{$css}' /><br />";

        echo "<input type = 'text' name = 'css[]' /><br />";

    ?>
    <input type = 'text' name = 'favicon' value = '<?php echo $template['favicon'];?>' />
    <input type = 'submit' />
</form>
        </div>
    </div>
</div>