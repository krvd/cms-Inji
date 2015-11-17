<h2>Оплата счета №<?= $pay->id; ?></h2>
Сумма к оплате: <b><?= $pay->sum; ?> <?= $pay->currency->acronym(); ?></b>
<h3>Выберите удобный способ оплаты и валюту</h3>
<div class="row">
  <?php
  foreach ($merchants as $merchant) {
      $allowCurrencies = $merchant->allowCurrencies($pay);
      if (!$allowCurrencies) {
          continue;
      }
      ?>
      <div class="col-xs-2 text-center">
        <img src="<?= $merchant->image ? $merchant->image->path : '/static/system/images/no-image.png'; ?>" class="img-responsive" />
        <h4><?= $merchant->name(); ?></h4>
      </div>
      <div class="col-xs-10 text-left">
        <?php
        foreach ($allowCurrencies as $allowCurrency) {
            switch ($allowCurrency['type']) {
                case 'transfer':
                    $sum = $pay->sum / $allowCurrency['transfer']->rate;
                    break;
                default:
                    $sum = $pay->sum;
                    break;
            }
            ?>
            <a class="btn btn-default btn-lg" href ="/money/merchants/go/<?= $pay->id; ?>/<?= $merchant->id; ?>/<?= $allowCurrency['currency']->id; ?>"><?=$sum;?> <?= $allowCurrency['currency']->acronym(); ?></a>
            <?php
        }
        ?>
      </div>
      <?php
  }
  ?>
</div>
