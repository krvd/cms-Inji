<div class="ecommerce">
  <div class="row">
    <div class="col-md-3 category-sidebar">
      <div class="sidebar-block">
        <div class="head">Категории</div>
        <div class="items">
          <?php $this->widget('Ecommerce\categorys'); ?>
        </div>
      </div>
      <div class="sidebar-block">
        <div class="head">Фильтры</div>
        <div class="items">
          <?php $this->widget('Ecommerce\filters'); ?>
        </div>
      </div>
    </div>
    <div class="col-md-9">
      <h2 class="category-name"><?= $category ? $category->name : 'Каталог продукции'; ?></h2>
      <div class="items-icons">	
        <div class="row">	
          <?php
          $i = 0;
          foreach ($items as $item) {
              ?>
              <div class="col-sm-4">		
                <?php $this->widget('Ecommerce\items/item', ['item' => $item]); ?>
              </div>
              <?php
              if (!( ++$i % 3)) {
                  echo '</div><div class="row">';
              }
          }
          ?>
        </div>
      </div>
      <div class="text-center">
        <?= $pages->draw(); ?>
      </div>
    </div>
  </div>
</div>
