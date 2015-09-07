<h1>Настройки магазина</h1>

<div>
    <!-- Nav tabs -->
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">Настройки</a></li>
        <li role="presentation"><a href="#itemOptions" aria-controls="itemOptions" role="tab" data-toggle="tab">Параметры товаров</a></li>
        <li role="presentation"><a href="#delivery" aria-controls="delivery" role="tab" data-toggle="tab">Варианты доставки</a></li>
        <li role="presentation"><a href="#payType" aria-controls="payType" role="tab" data-toggle="tab">Способы оплаты</a></li>
        <li role="presentation"><a href="#warehouse" aria-controls="warehouse" role="tab" data-toggle="tab">Склады</a></li>
        <li role="presentation"><a href="#unit" aria-controls="unit" role="tab" data-toggle="tab">Единицы измерения</a></li>
        <li role="presentation"><a href="#priceType" aria-controls="priceType" role="tab" data-toggle="tab">Типы цен</a></li>
        <li role="presentation"><a href="#itemType" aria-controls="itemType" role="tab" data-toggle="tab">Типы товаров</a></li>
        <li role="presentation"><a href="#UserAddsField" aria-controls="UserAddsField" role="tab" data-toggle="tab">Поля корзины</a></li>
        <li role="presentation"><a href="#settings" aria-controls="settings" role="tab" data-toggle="tab">Операции</a></li>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane fade in active" id="home">
            <?php
            $form = new Ui\Form();
            $form->begin();
            $form->input('checkbox', 'config[view_empty_warehouse]', 'Показывать отсутствующие товары', ['value' => App::$cur->ecommerce->config['view_empty_warehouse']]);
            $form->input('checkbox', 'config[view_empty_image]', 'Показывать товары без изображения', ['value' => App::$cur->ecommerce->config['view_empty_image']]);
            $form->input('checkbox', 'config[sell_empty_warehouse]', 'Продавать отсутствующие товары', ['value' => App::$cur->ecommerce->config['sell_empty_warehouse']]);
            $form->input('checkbox', 'config[sell_over_warehouse]', 'Продавать сверх остатоков на складе', ['value' => App::$cur->ecommerce->config['sell_over_warehouse']]);
            $form->end('Сохранить');
            ?>
        </div>
        <div role="tabpanel" class="tab-pane fade" id="itemOptions">
            <?php
            $dataManager = new Ui\DataManager('Ecommerce\Item\Option');
            $dataManager->draw();
            ?>
        </div>
        <div role="tabpanel" class="tab-pane fade" id="delivery">
            <?php
            $dataManager = new Ui\DataManager('Ecommerce\Delivery');
            $dataManager->draw();
            ?>
        </div>
        <div role="tabpanel" class="tab-pane fade" id="payType">
            <?php
            $dataManager = new Ui\DataManager('Ecommerce\PayType');
            $dataManager->draw();
            ?>
        </div>
        <div role="tabpanel" class="tab-pane fade" id="warehouse">
            <?php
            $dataManager = new Ui\DataManager('Ecommerce\Warehouse');
            $dataManager->draw();
            ?>
        </div>
        <div role="tabpanel" class="tab-pane fade" id="unit">
            <?php
            $dataManager = new Ui\DataManager('Ecommerce\Unit');
            $dataManager->draw();
            ?>
        </div>
        <div role="tabpanel" class="tab-pane fade" id="priceType">
            <?php
            $dataManager = new Ui\DataManager('Ecommerce\Item\Offer\Price\Type');
            $dataManager->draw();
            ?>
        </div>
        <div role="tabpanel" class="tab-pane fade" id="itemType">
            <?php
            $dataManager = new Ui\DataManager('Ecommerce\Item\Type');
            $dataManager->draw();
            ?>
        </div>
        <div role="tabpanel" class="tab-pane fade" id="UserAddsField">
            <?php
            $dataManager = new Ui\DataManager('Ecommerce\UserAdds\Field');
            $dataManager->draw();
            ?>
        </div>
        <div role="tabpanel" class="tab-pane fade" id="settings">
            <a href ="/admin/ecommerce/recalcTree" class = 'btn btn-primary btn-sm'>Обновить хранимую структуру</a>
            <a href ="/admin/ecommerce/reSearchIndex" class = 'btn btn-primary btn-sm'>Обновить поисковый индекс</a>
            <a href ="/admin/ecommerce/reBlockIndex" class = 'btn btn-primary btn-sm'>Обновить индекс блокировок</a>
        </div>
    </div>
</div>


