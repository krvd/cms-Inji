<div class="product-category2">
    <h3 class="text-left"><?php echo $catalog['catalog_name']; ?></h3>
    <?php $this->view->widget('breadcrumb');?>
    <div class="products">	
        <?php
        foreach ($items as $item) {
            ?>
            <div class="">		
                <h5><a href="/catalog/view/<?php echo $item['ci_id']; ?>"><?php echo $item['ci_name']; ?></a></h5>
            </div>
            <?php
        }
        ?>
    </div>
</div>