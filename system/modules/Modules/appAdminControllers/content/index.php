<?php
foreach ($systemModules as $module){
    var_dump(Module::getInfo($module));
}