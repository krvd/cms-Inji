<div class="filters">
  <form>
    <?php
    $options = \Ecommerce\Item\Option::getList(['where' => ['item_option_searchable', 1]]);
    foreach ($options as $option) {
        ?>
        <div class="filter">    
          <h4><?= $option->name; ?></h4>
          <?php
          foreach ($option->items as $item) {
              ?>

              <div class="radio">
                <label>
                  <input type="radio" name = 'filters[options][<?= $option->id; ?>]' value ="<?= $item->id; ?>" <?= !empty($_GET['filters']['options'][$option->id]) && $_GET['filters']['options'][$option->id] == $item->id ? 'checked' : ''; ?>>
                  <?= $item->value; ?>
                </label>
              </div>

              <?php
          }
          ?>
        </div>
        <?php
    }
    $min = App::$cur->ecommerce->getItems(['sort' => ['price' => 'asc'], 'count' => 1, 'key' => false]);
    $max = App::$cur->ecommerce->getItems(['sort' => ['price' => 'desc'], 'count' => 1, 'key' => false]);
    if ($min && $max) {
        ?>
        <h4>Фильтр по цене</h4>
        <div class="row">
          <div class="col-sm-6">от <input type="text" name = 'filters[price][min]' value ="<?= !empty($_GET['filters']['price']['min']) ? $_GET['filters']['price']['min'] : $min[0]->getPrice()->price; ?>" class="form-control" /></div>
          <div class="col-sm-6">до <input type="text" name = 'filters[price][max]' value ="<?= !empty($_GET['filters']['price']['max']) ? $_GET['filters']['price']['max'] : $max[0]->getPrice()->price; ?>" class="form-control" /></div>
        </div>
        <?php
    }
    ?>
    <hr />
    <button class="btn btn-primary">Применить</button>
  </form>
</div>