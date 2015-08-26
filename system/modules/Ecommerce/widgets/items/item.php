<div class="item">		
    <h4 class="item-name">
        <a href="/ecommerce/view/<?= $item->id; ?>">
            <?= $item->name(); ?>
        </a>
    </h4>
    <a href="/ecommerce/view/<?= $item->id; ?>">
        <img src="<?= $item->image ? $item->image->path : '/static/system/images/no-image.png'; ?>?resize=220x220&resize_crop=q" class="img-responsive"/>
    </a>
    <div class="item-actions">
        <div class="btn-group btn-group-justified">
            <a class="btn btn-default item-price"><?= number_format($item->getPrice()->price, 2, '.', ' '); ?> руб</a>
            <a class="btn btn-primary item-addtocart" onclick="inji.Ecommerce.Cart.addItem(<?= $item->getPrice()->id; ?>, 1);"><i class="glyphicon glyphicon-shopping-cart"></i></a>
        </div>
    </div>
</div>