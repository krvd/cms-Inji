<div class="ecommerce">
    <div class="row">
        <div class="col-xs-3">
            <?php //$this->widget('Ecommerce\filters'); ?>
        </div>
        <div class="col-xs-9">
            <div class="detail_item">
                <h1><?= $item->name(); ?></h1>
                <div class="row">
                    <div class="col-sm-5">
                        <img src="<?= $item->image ? $item->image->path : '/static/system/images/no-image.png'; ?>?resize=220x220&resize_crop=q" class="img-responsive"/>
                    </div>
                    <div class="col-sm-7">
                        <?= $item->decription; ?>
                        <div class="item-actions">
                            <div class="btn btn-default item-price">
                                <?= number_format($item->getPrice()->price, 2, '.', ' '); ?>
                            </div>
                            <div class="item-currency">
                                руб
                            </div>
                            <div class="btn btn-primary item-addtocart">
                                <i class="glyphicon glyphicon-shopping-cart"></i> Добавить в корзину
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>