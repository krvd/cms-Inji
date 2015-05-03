<!--Begin Datatables-->
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <header>
                <div class="icons">
                    <i class="icon-table"></i>
                </div>
                <h5>Меню</h5>
                <div class="toolbar" style = 'margin-right:200px'>
                    <a href = '<?php echo $this->url->current('create'); ?>' class="btn btn-success btn-sm" >Создать меню</a>
                </div>
            </header>
            <div id="collapse4" class="body">
                <table id="dataTable" class="table table-bordered table-condensed table-hover table-striped">
                    <thead>
                        <tr>
                            <th>Меню</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($menus as $menu)
                            echo "<tr><td><a href = '".$this->url->current('items/'.$menu['mg_id'])."'>{$menu['mg_name']}</a></td><td><a href = '".$this->url->current('set_default/'.$menu['mg_id'])."'>Редактировать</a></td></tr>";
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div><!-- /.row -->

<!--End Datatables-->
<script>
    $(function() {
        metisTable();
    });
</script>
