<div class="row">
    <div class="col-sm-4 blockPreset">
        <div class ='block' data-code='{WIDGET:headMenu}'>Главное меню</div>
        <div class ='block' data-code='{WIDGET:header}'>Шапка</div>
        <div class ='block' data-code='{CONTENT}'>Контент</div>
        <div class ='block' data-code='{WIDGET:sidebar}'>Сайдбар</div>
        <div class ='block' data-code='{WIDGET:newstories}'>Новые истории</div>
        <div class ='block' data-code='{WIDGET:footer}'>Подвал</div>
        
    </div>
    <div class="col-sm-8 blockMap">
        <div class ='pull-right'>
            <a type='button' class="btn btn-primary btn-sm" onclick="blockDrop.addRow('.blockMap .rows');">Добавить ряд</a>
        </div>
        <div class ='clearfix'></div>
        <div class ='rows'>
            <?= !empty($map) ? $map : ''; ?>
        </div>
        <?= !empty($map) ? "<script>blockDrop.initActual('.blockMap .rows')</script>" : ''; ?>
    </div>
</div>