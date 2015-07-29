<?php

class Ecommerce_HELPER extends Module {

    function abc($liters) {
		$get = $_GET;
        echo '<div class = "text-center">';
        $btn_group = $this->bootstrap->widget('btn_group');
        foreach ($liters as $liter) {
            $active = false;
            if ( !empty($_GET['search']) && $_GET['search'] == $liter['liter'])
                $active = true;
            $get['search'] = $liter;
            $btn_group->add_item($liter['liter'], "?" . http_build_query($get), $active);
        }
        $btn_group->draw();
        echo '</div>';
    }

    function limits($limits) {
        $get = $_GET;
        ?>
        <div class = 'btn-group'>
            <span class = 'btn btn-default disabled'>Показывать по</span>
            <div class="btn-group">
                <a type="button" class="btn btn-default dropdown-toggle"  data-toggle="dropdown">
                    <?php
                    if (!empty($get['limit'])) {
                        echo $get['limit'];
                    } else {
                        echo current($limits);
                    }
                    ?>
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                    <?php
                    foreach ($limits as $limit) {
                        $get['limit'] = $limit;
                        echo "<li><a href = '?" . http_build_query($get) . "'>{$limit}</a></li>";
                    }
                    ?>
                </ul>
            </div>
        </div>
        <?php
    }

    function sorts($sorts) {
        $get = $_GET;
        ?>
        <div class = 'btn-group'>
            <span class = 'btn btn-default disabled'>Сортировать по </span>
            <div class="btn-group">
                <a type="button" class="btn btn-default dropdown-toggle"  data-toggle="dropdown">
                    <?php
                    if (!empty($get['sort']) && !empty($sorts[$get['sort']])) {
                        echo $sorts[$get['sort']];
                    } else {
                        echo current($sorts);
                    }
                    ?>
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                    <?php
                    foreach ($sorts as $key => $sort) {
                        $get['sort'] = $key;
                        echo "<li><a href = '?" . http_build_query($get) . "'>{$sort}</a></li>";
                    }
                    ?>
                </ul>
            </div>
        </div>
        <?php
    }

    function show_items($items) {
        if ($items) {
            $i = 0;
            foreach ($items as $item) {
                $params = $this->ecommerce->get_item_params($item['ci_id']);
                $prices = $this->ecommerce->get_prices($item['ci_id']);
                reset($prices);
                $price = $prices[key($prices)];
                $photo = $this->Files->get($params['photo']['cip_value']);
                if ($i == 0)
                    echo '<div class = "row">';
                ?>
                <div class = 'col-lg-4 col-md-4 col-sm-12'>
                    <div class = 'panel panel-primary'>
                        <div class="panel-heading"><?php echo $item['ci_name']; ?></div>
                        <a href="/catalog/view/<?php echo $item['ci_id']; ?>" class="big_photo">
                            <img src="<?php echo $photo['file_path']; ?>?resize=531x250&resize_crop=c" style="width:100%;">
                        </a>	
                        <div class="panel-body">
                            <?php if ($params['sale']['cip_value'] && strtotime($params['sale']['cip_value']) > time()) { ?>
                                <div style = 'right: 10px;bottom: 37px;background:#fff;padding:5px;position:absolute;'>До конца акции<br /><span id = 'sale_timer<?php echo $item['ci_id']; ?>'></span></div>
                                <script>
                                    var d = [1, 7, 6, 5, 4, 3, 2];
                                    var today = new Date();
                                    var end<?php echo $item['ci_id']; ?> = new Date(<?php echo date('Y,m-1,d', strtotime($params['sale']['cip_value'])); ?>, 0, 0, 0);
                                    var _second = 1000;
                                    var _minute = _second * 60;
                                    var _hour = _minute * 60;
                                    var _day = _hour * 24;
                                    var timer;

                                    function showRemaining<?php echo $item['ci_id']; ?>() {
                                        var now = new Date();
                                        var distance = end<?php echo $item['ci_id']; ?> - now;
                                        if (distance < 0) {
                                            document.getElementById("countdown").innerHTML = "Акция окончена";
                                            return;
                                        }
                                        var days = Math.floor(distance / _day);
                                        var hours = Math.floor((distance % _day) / _hour);
                                        if (hours < 10)
                                            hours = '0' + hours;
                                        var minutes = Math.floor((distance % _hour) / _minute);
                                        if (minutes < 10)
                                            minutes = '0' + minutes;
                                        var seconds = Math.floor((distance % _minute) / _second);
                                        if (seconds < 10)
                                            seconds = '0' + seconds;
                                        document.getElementById('sale_timer<?php echo $item['ci_id']; ?>').innerHTML = "" + days + " дн. " + hours + ":" + minutes + ":" + seconds + "";
                                    }

                                    timer = setInterval(showRemaining<?php echo $item['ci_id']; ?>, 1000);

                                </script>
                                <?php
                            }
                            ?>

                            <div class="btn-group  btn-group-justified">
                                <?php
                                $price = current($prices);
                                ?>
                                <span class="btn btn-primary curitemprice disabled" ciprice_id = '<?php echo $price['ciprice_id']; ?>' price = '<?php echo $price['ciprice_price']; ?>'><?php echo $price['ciprice_price']; ?> р.</span>
                                <div class="btn-group">
                                    <a type="button" class="btn btn-default dropdown-toggle"  data-toggle="dropdown">
                                        <?php
                                        echo $price['ciprice_name'];
                                        ?>
                                        <span class="caret"></span>
                                    </a>
                                    <ul class="dropdown-menu changeprice">
                                        <?php foreach ($prices as $price) { ?>
                                            <li><a href = '#' ciprice_id = '<?php echo $price['ciprice_id']; ?>' price = '<?php echo $price['ciprice_price']; ?>'><?php echo $price['ciprice_name']; ?></a></li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </div>
                            <br />
                            <div class="btn-group btn-group-justified">
                                <a class = 'btn btn-default' href="/catalog/view/<?php echo $item['ci_id']; ?>" >Подробнее</a>
                                <a class = 'btn btn-success buyitem' href="/catalog/view/<?php echo $item['ci_id']; ?>" ci_id = "<?php echo $item['ci_id']; ?>" data-loading-text="Подождите">В корзину</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                if (!( ++$i % 3) && $i != count($items))
                    echo '</div><div class = "row">';
            }
            ?>
            </div>
            <?php
        }
    }

}
?>
