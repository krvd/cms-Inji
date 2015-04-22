<!--Begin Datatables-->
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <header>
                <div class="icons">
                    <i class="fa fa-table"></i>
                </div>
                <h5>Шаблоны</h5>
                <div class="toolbar">
                    <a href = '/admin/View/create' class="btn btn-success btn-sm" >Создать шаблон</a>
                </div>
            </header>
            <div id="collapse4" class="body">
                <table id="dataTable" class="table table-bordered table-condensed table-hover table-striped">
                    <thead>
                        <tr>
                            <th>Шаблон</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!empty($templates['install_templates']))
                            foreach ($templates['install_templates'] as $name=> $template) {
                                echo "<tr><td><a href = '/admin/View/edit/{$name}'>{$template}</a></td><td>";
                                if (!empty($templates['current']) && $templates['current'] == $name) {
                                    echo 'Шаблон по умолчанию';
                                } else {
                                    echo "<a href = '/admin/View/set_default/{$name}'>установить по умолчанию</a>";
                                }
                                echo "</td></tr>";
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div><!-- /.row -->

<!--End Datatables-->
