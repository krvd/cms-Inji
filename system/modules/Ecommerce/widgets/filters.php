<div class="filters">
  <form>
    <h4>Фильтр по цене</h4>
    <div class="row">
      <?php
      $min = App::$cur->ecommerce->getItems(['sort' => ['price' => 'asc'], 'count' => 1]);
      $max = App::$cur->ecommerce->getItems(['sort' => ['price' => 'desc'], 'count' => 1]);
      ?>
      <div class="col-sm-6">от <input type="text" name = 'filters[price][min]' value ="<?= !empty($_GET['filters']['price']['min']) ? $_GET['filters']['price']['min'] : $min[key($min)]->getPrice()->price; ?>" class="form-control" /></div>
      <div class="col-sm-6">до <input type="text" name = 'filters[price][max]' value ="<?= !empty($_GET['filters']['price']['max']) ? $_GET['filters']['price']['max'] : $max[key($max)]->getPrice()->price; ?>" class="form-control" /></div>
    </div>
    <hr />
    <button class="btn btn-primary">Применить</button>
  </form>
</div>