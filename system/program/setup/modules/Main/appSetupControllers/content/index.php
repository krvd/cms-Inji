<div class="row">
  <div class="col-lg-12">
    <?php
    $dataManager = new Ui\DataManager('Apps\App', 'setup');
    $dataManager->draw();
    ?>
  </div>
  <div class="col-lg-12">
    <?php
    $dataManager = new Ui\DataManager('Db\Options', 'setup');
    $dataManager->draw();
    ?>
  </div>
</div>
<div class="pull-right">
  <a class="btn btn-primary" href ="/install/main/modules">Далее</a>
  <div class="clearfix"></div>
</div>