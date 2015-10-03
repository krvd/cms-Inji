<h1>Настройки магазина</h1>
<!-- Nav tabs -->
<ul class="nav nav-tabs" role="tablist">
  <li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">Настройки</a></li>
  <?php
  $dataManagers = [];
  foreach ($managers as $manager) {
      $dataManager = new Ui\DataManager($manager);
      $dataManagers[$manager] = $dataManager;
      $code = 'tab_' . str_replace('\\', '_', $manager);
      echo "<li role='presentation'><a href='#{$code}' aria-controls='{$code}' role='tab' data-toggle='tab'>{$dataManager->name}</a></li>";
  }
  ?>
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
    $form->input('hidden', 'config[save]', '', ['value' => 1]);
    $form->end('Сохранить');
    ?>
  </div>
  <?php
  foreach ($dataManagers as $manager => $dataManager) {
      $code = 'tab_' . str_replace('\\', '_', $manager);
      ?>
      <div role="tabpanel" class="tab-pane fade" id="<?= $code; ?>">
        <?php
        $dataManager->draw();
        ?>
      </div>
      <?php
  }
  ?>
</div>


