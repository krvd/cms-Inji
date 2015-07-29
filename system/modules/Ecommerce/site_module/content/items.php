<div class="row">
    <div class="col-md-3">
        <?php $this->view->widget('catalogMenu'); ?>
    </div>

    <div class = "col-md-9">
        <form>
            <div class ='row'>      
                <div class ='col-sm-8'>      
                    <div class ="form-group">
                        <label>Поиск по названию</label>
                        <input autocomplete="off" id ='searchInput' class ='form-control' type ="text" name ="search" value = '<?= (!empty($_GET['search'])) ? $_GET['search'] : ''; ?>'/>
                    </div>

                </div>
                <div class ='col-sm-4'>
                    <div class ='form-group'>   
                        <?php
                        if (!empty($catalog)) {
                            ?>
                            <div class="checkbox" style = 'margin:0;margin-bottom:5px;'>
                                <label>
                                    <input type="checkbox" name = 'inCatalog' <?= (!empty($_GET['search']) && empty($_GET['inCatalog'])) ? '' : 'checked'; ?> value = 1> В текущем каталоге
                                </label>
                            </div>

                        <?php } else { ?>
                            <label>&nbsp;</label>
                        <?php } ?>
                        <button class = 'btn btn-primary btn-block'>Найти</button>
                    </div>
                </div>
            </div>
        </form>
        <script>
            $.get('/ecommerce/autoComlete', function (data) {
                var $input = $('#searchInput');
                $input.typeahead({
                    source: data,
                    matcher: function (item) {
                        if (item.name.toLowerCase().indexOf(this.query) !== -1 || item.translit.toLowerCase().indexOf(this.query) !== -1) {
                            return true
                        }
                    },
                    updater: function (item) {
                        return item.name;
                    },
                    displayText: function (item) {

                        return item.name;
                    }
                });
            }, 'json');

        </script>
        <?php
        if (!empty($catalog)) {
            ?>
            <?php
        } else {
            ?>
            <!--<div class = "row carousel-holder">

                <div class = "col-md-12">
                    <div id = "carousel-example-generic" class = "carousel slide" data-ride = "carousel">
                        <ol class = "carousel-indicators">
                            <li data-target = "#carousel-example-generic" data-slide-to = "0" class = "active"></li>
                            <li data-target = "#carousel-example-generic" data-slide-to = "1"></li>
                            <li data-target = "#carousel-example-generic" data-slide-to = "2"></li>
                        </ol>
                        <div class = "carousel-inner">
                            <div class = "item active">
                                <img class = "slide-image" src = "http://placehold.it/800x300" alt = "">
                            </div>
                            <div class = "item">
                                <img class = "slide-image" src = "http://placehold.it/800x300" alt = "">
                            </div>
                            <div class = "item">
                                <img class = "slide-image" src = "http://placehold.it/800x300" alt = "">
                            </div>
                        </div>
                        <a class = "left carousel-control" href = "#carousel-example-generic" data-slide = "prev">
                            <span class = "glyphicon glyphicon-chevron-left"></span>
                        </a>
                        <a class = "right carousel-control" href = "#carousel-example-generic" data-slide = "next">
                            <span class = "glyphicon glyphicon-chevron-right"></span>
                        </a>
                    </div>
                </div>

            </div>-->
            <?php
        }
        ?>
        <div class = "row">
            <?php
            $i = 0;
            foreach ($items as $item) {
                $item['params'] = $this->ecommerce->get_item_params($item['ci_id']);
                $item['prices'] = $this->ecommerce->get_prices($item['ci_id']);
                //var_dump($item['params']);

                if (!empty($item['params']['itemImage']['cip_value'])) {
                    $file = $this->files->get($item['params']['itemImage']['cip_value']);
                    $path = $file['file_path'];
                } else {
                    $path = '/static/images/no-image.png';
                }
                ?>
                <div class = "col-sm-4 col-lg-4 col-md-4">
                    <div class = "thumbnail">
                        <img src = "<?= $path; ?>?resize=350x350" alt = "">
                        <div class = "caption">
                            <h4><a href = "/ecommerce/view/<?= $item['ci_id']; ?>"><?= (empty($item['params']['3ec57698-662b-11e4-9462-80c16e818121']['cip_value'])) ? $item['ci_name'] : $item['params']['3ec57698-662b-11e4-9462-80c16e818121']['cip_value']; ?></a></h4>
                            <?php
                            foreach ($item['prices'] as $price) {
                                echo "<h5 class ='text-right'>{$price['ciprice_name']}: {$price['ciprice_price']} {$price['ciprice_curency']}</h4>";
                            }
                            ?>
                        </div>
                        <div class='btn-group btn-group-justified'>
                            <a href ='/ecommerce/view/<?= $item['ci_id']; ?>' class = 'btn btn-primary'>Подробнее</a>
                            <a href ='#' class = 'btn btn-primary buyitem' data-ci_id="<?= $item['ci_id']; ?>"><i class = 'glyphicon glyphicon-shopping-cart'></i> В корзину</a>
                        </div>
                    </div>
                </div>
                <?php
                if (!( ++$i % 3)) {
                    echo "<div class ='clearfix'></div>";
                }
            }
            ?>
        </div>
        <div class="text-center">
            <ul class="pagination pagination-centered margin-none"><?php
            if (!empty($catalog['catalog_id'])) {
                $catal_id = $catalog['catalog_id'];
            } else
                $catal_id = '';
            $data = array(
                'limit' => $limit,
                'sort' => $sort,
                'search' => $search
            );
            if ($page > 1) {
                echo "<li><a href = '/ecommerce/{$catal_id}?page=1&" . http_build_query($data) . "'>&lArr;</a>";
                echo "<li><a href = '/ecommerce/{$catal_id}?page=" . ($page - 1) . "&" . http_build_query($data) . "''>&larr;</a>";
            }

            for ($i = 1; $i <= $pages; $i++) {
                if (( $i >= $page - 3 && $i <= $page + 3)) {
                    echo '<li ';
                    if ($page == $i)
                        echo 'class = "active"';
                    echo ">";
                    echo "<a href = '/ecommerce/{$catal_id}?page={$i}&" . http_build_query($data) . "''>{$i}</a></li>";
                }
                if ($i == $page - 7 || $i == $page + 7)
                    echo "<li><a href = '/ecommerce/{$catal_id}?page={$i}&" . http_build_query($data) . "''>...</a>";
            }
            if ($page < $pages) {
                echo "<li><a href = '/ecommerce/{$catal_id}?page=" . ($page + 1) . "&" . http_build_query($data) . "''>&rarr;</a>";
                echo "<li><a href = '/ecommerce/{$catal_id}?page={$pages}&" . http_build_query($data) . "''>&rArr;</a>";
            }
            ?>				
            </ul>


        </div>
    </div>
</div>

