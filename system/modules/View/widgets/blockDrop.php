<div class="row">
    <div class="col-sm-3 block-preset">
        <div class ='block' data-type="single" data-code='{WIDGET:headMenu}'>Главное меню</div>
        <div class ='block' data-type="single" data-code='{WIDGET:header}'>Шапка</div>
        <div class ='block' data-type="single" data-code='{CONTENT}'>Контент</div>
        <div class ='block' data-type="single" data-code='{WIDGET:sidebar}'>Сайдбар</div>
        <div class ='block' data-type="single" data-code='{WIDGET:newstories}'>Новые истории</div>
        <div class ='block' data-type="single" data-code='{WIDGET:footer}'>Подвал</div>

    </div>
    <div class="col-sm-9 blockMap">
        <!--<div class ='pull-right'>
            <a type='button' class="btn btn-primary btn-sm" onclick="blockDrop.addRow('.blockMap .rows');">Добавить ряд</a>
        </div>
        <div class ='clearfix'></div>-->
        <div class ='rows'>
            <?= !empty($map) ? $map : ''; ?>
        </div>
        <?= "<script>inji.onLoad(function(){blockDrop.initActual('.blockMap .rows')});</script>"; ?>
    </div>
</div>