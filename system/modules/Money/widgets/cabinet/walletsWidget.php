<big>Кошельки</big><br />
<?php
$wallets = App::$cur->money->getUserWallets();
foreach ($wallets as $wallet) {
    ?>
    <b><?= number_format($wallet->amount, 4, '.', ' '); ?></b> <?= $wallet->currency->acronym(); ?><br />
    <?php
}
?>
<a href ='/money/refill'>Пополнить</a>
<a href ='/money/exchange'>Обменять</a>