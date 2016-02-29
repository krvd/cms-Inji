<div class="items-icons">	
  <div class="row">	
    <?php
    $i = 0;
    foreach ($items as $item) {
        ?>
        <div class="col-sm-4">		
          <?php $this->widget('Ecommerce\items/item-icon', ['item' => $item]); ?>
        </div>
        <?php
        if (!( ++$i % 3)) {
            echo '</div><div class="row">';
        }
    }
    ?>
  </div>
</div>