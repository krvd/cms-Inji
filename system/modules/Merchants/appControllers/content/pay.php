<h2>Выберите удобный способ оплаты</h2>
<div class="row">
  <?php
  foreach ($merchants as $merchant) {
      ?>
      <div class="col-sm-2 text-center">
        <a href="/merchants/go/<?= $pay_id; ?>/<?= $merchant->id; ?>">
          <img src="<?= $merchant->image ? $merchant->image->path : '/static/system/images/no-image.png'; ?>" class="img-responsive" />
          <h4><?= $merchant->name; ?></h4>
        </a>
      </div>
      <?php
  }
  ?>
</div>
