<h2>Оплата счета №<?= $pay->id; ?></h2>
Сумма к оплате: <b><?= $pay->sum; ?> <?= $pay->currency->acronym(); ?></b>
<h3>Выберите удобный способ оплаты и валюту</h3>
<div class="row">
  <?php
  $wallets = App::$cur->money->getUserWallets();
  if (!empty($wallets[$pay->currency_id]) && $pay->type != 'refill') {
      ?>
      <div class="col-xs-2 text-center">
        <img src="/static/system/images/no-image.png" class="img-responsive" />
        <h4>Личный счет</h4>
      </div>
      <div class="col-xs-10 text-left">
        <a class="btn btn-default btn-lg" href ="/money/walletPay/<?= $pay->id; ?>/<?= $wallets[$pay->currency_id]->id; ?>"><?= $pay->sum; ?> <?= $pay->currency->acronym(); ?></a>
      </div>
    </div>
    <div class="row">

      <?php
  }
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
      <div class="col-xs-10 col-md-4 text-left">
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
            <a class="btn btn-primary btn-lg" href ="/money/merchants/go/<?= $pay->id; ?>/<?= $merchant->id; ?>/<?= $allowCurrency['currency']->id; ?>">Оплатить <?= $sum; ?> <?= $allowCurrency['currency']->acronym(); ?></a>
            <?php
        }
        ?>
      </div>
      <div class="hidden-xs hidden-sm col-md-6 text-left">
        <?= $merchant->previewImage ? '<img src="' . $merchant->previewImage->path . '" class="img-responsive" />' : ''; ?>
      </div>
      <?php
  }
  ?>
</div>
