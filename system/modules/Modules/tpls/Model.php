<?="<?php\n";?>

<?="namespace {$module};\n";?>

class <?=$codeName;?> extends \Model {
    //modelParams<?php
    if(!empty($name)){
        echo "\n    static ".'$objectName = \''.$name."';";
    }
    if(!empty($labels)){
        echo "\n    static ".'$labels = ['.Config::buildPhpArray($labels,1)."\n    ];";
    }
    if(!empty($cols)){
        echo "\n    static ".'$cols = ['.Config::buildPhpArray($cols,1)."\n    ];";
    }
    ?>
    //!modelParams
    //modelBody
    //!modelBody
}
