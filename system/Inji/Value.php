<?php

class Value {

    public $valueKey = '';
    public $model = null;
    public $type = 'string';

    function __construct($model, $key) {
        $this->model = $model;
        $this->valueKey = $key;
    }

    function forView() {
        $modelName = get_class($this->model);
        $colInfo = $modelName::getColInfo($this->valueKey);
        $type = !empty($colInfo['colParams']['type']) ? $colInfo['colParams']['type'] : 'string';
        switch ($type) {
            case'select':
                switch ($colInfo['colParams']['source']) {
                    case 'array':
                        return !empty($colInfo['colParams']['sourceArray'][$this->model->{$this->valueKey}]) ? $colInfo['colParams']['sourceArray'][$this->model->{$this->valueKey}] : 'Не задано';
                    case 'method':
                        $values = $colInfo['colParams']['module']->$inputParams['method']();
                        return !empty($values[$this->model->{$this->valueKey}]) ? $values[$this->model->{$this->valueKey}] : 'Не задано';
                    case 'relation':
                        $relations = $colInfo['modelName']::relations();
                        $relValue = $relations[$colInfo['colParams']['relation']]['model']::get($this->model->{$this->valueKey});
                        return $relValue ? $relValue->name() : 'Не задано';
                }
                $value = !empty($_GET['datamanagerFilters'][$col]['value']) ? $_GET['datamanagerFilters'][$col]['value'] : '';
                ?>
                <div class="filter_form_field filter_select">
                    <?php
                    $form->input('select', "datamanagerFilters[{$col}][value]", $colInfo['label'], ['value' => $value, 'values' => $values, 'noContainer' => true])
                    ?>
                </div>
                <?php
                break;
            case 'image':
                $file = Files\File::get($this->model->{$this->valueKey});
                if ($file) {
                    return '<img src="' . $file->path . '?resize=60x120" />';
                } else {
                    return '<img src="/static/system/images/no-image.png?resize=60x120" />';
                }
                break;
            case 'bool':
                return $this->model->{$this->valueKey} ? 'Да' : 'Нет';

            default:
                return $this->model->{$this->valueKey};
        }
    }

}
