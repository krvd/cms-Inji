<!--Begin Datatables-->
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <header>
                <div class="icons">
                    <i class="fa fa-list"></i>
                </div>
                <h5>Меню</h5>
                <div class="toolbar">
                    <a href = '/admin/Menu/add_item/<?php echo $menu['mg_id']; ?>' class="btn btn-success btn-sm" >Добавить элемент</a>
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
                    <tbody class = 'sortable'>
                        <?php
                        foreach ($items as $item)
                            echo "<tr data-id = '{$item['mi_id']}'><td><a href = '/admin/Menu/edit/{$item['mi_id']}'>{$item['mi_name']}</a></td><td><a href = '/admin/Menu/edit/{$item['mi_id']}'>Редактировать</a> <a href = '/admin/Menu/del/{$item['mi_id']}'>удалить</a></td></tr>";
                        ?>
                    </tbody>
                </table>
                <script>
                    $(function () {
                        $(".sortable").sortable({
                            placeholder: "ui-state-highlight",
                            stop: function (event, ui) {
                                data = {};
                                data.data = {};
                                data.url = '/admin/Menu/sort_items';
                                ids = $('.sortable tr');
                                i = 0;
                                while (ids[i])
                                    data.data[i] = $(ids[i++]).data('id');
                                $.ajax(data);
                            }
                        });
                    });
                </script>
                <style>
                    .sortable td {
                        cursor: move
                    }
                    .ui-state-highlight { height: 20px;}
                </style>
            </div>
        </div>
    </div>
</div><!-- /.row -->

<!--End Datatables-->