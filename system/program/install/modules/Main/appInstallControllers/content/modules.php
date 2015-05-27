<h1>Выбор модулей</h1>
<form>
    <?php
    $systemModules = array_slice(scandir(INJI_SYSTEM_DIR . '/modules'), 2);
    foreach ($systemModules as $module) {
        $info = Module::getInfo($module);
        if(!$info)
            continue;
        ?>
        <div class ="form-group">
            <div class="checkbox">
                <label>
                    <input type ="checkbox" name ="modules[]" value ="<?=$module;?>" /> <?=$info['name'];?>
                </label>
            </div>
        </div>
        <?php
    }
    ?>
    <button class="btn btn-primary">Установить</button>
</form>