<div class="row">	
    <div class="col-md-5">
        <?php
        if (!empty($item['params']['itemImage']['cip_value'])) {
            $file = $this->files->get($item['params']['itemImage']['cip_value']);
            $path = $file['file_path'];
        } else {
            $path = '/static/images/no-image.png';
        }
        ?>
        <img src="<?= $path; ?>?resize=400x1500" class ='img-responsive' />
    </div>
    <div class="col-md-7">
        <h1><?= (empty($item['params']['3ec57698-662b-11e4-9462-80c16e818121']['cip_value'])) ? $item['ci_name'] : $item['params']['3ec57698-662b-11e4-9462-80c16e818121']['cip_value']; ?></h1>
        <div><?= $item['params']['itemAbout']['cip_value']; ?></div>
        <div class="product-rating"><i class="fa fa-star gold"></i> <i class="fa fa-star gold"></i> <i class="fa fa-star gold"></i> <i class="fa fa-star gold"></i> <i class="fa fa-star-o"></i> </div>
        <hr>
        <?php
        foreach ($item['prices'] as $price) {
            echo "<h3 >{$price['ciprice_name']}: {$price['ciprice_price']} {$price['ciprice_curency']}</h4>";
        }
        ?>
        <div class ='pull-right'>На складе: <?=$warehouse['sum'];?></div>
        <hr>
        <div class="btn-group cart">
            <button type="button" class="btn btn-success buyitem" data-ci_id="<?=$item['ci_id'];?>">
                <i class ='glyphicon glyphicon-shopping-cart'></i> В корзину
            </button>
        </div>
    </div>
</div> 
<div class="row">		
    <div class="col-md-12 product-info">
        <ul id="myTab" class="nav nav-tabs nav_tabs">

            <li class="active"><a href="#service-one" data-toggle="tab">Свойства</a></li>

        </ul>
        <div id="myTabContent" class="tab-content">
            <div class="tab-pane fade in active" id="service-one">
                <br />
                <ul>
                    <?php
                    foreach ($item['params'] as $param) {
                        if (!$param['cio_view'] || !$param['cip_value'])
                            continue;
                        if ($param['cio_type'] == 'select') {
                            $options = json_decode($param['cio_advance'], true);
                            $value = $options['data'][$param['cip_value']];
                        } else {
                            $value = $param['cip_value'];
                        }
                        if (strpos($param['cio_name'], ':')) {
                            $paramName = substr($param['cio_name'], 0, strpos($param['cio_name'], ':'));
                        }
                        elseif (strpos($param['cio_name'], '(')) {
                            $paramName = substr($param['cio_name'], 0, strpos($param['cio_name'], '(') - 1);
                        }
                        else {
                            $paramName = $param['cio_name'];
                        }
                        echo "<li>{$paramName}: {$value}</li>";
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
</div>