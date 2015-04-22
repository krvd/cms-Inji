<!--Begin Datatables-->
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <header>
                <div class="icons">
                    <i class="fa fa-table"></i>
                </div>
                <h5>редактирование шаблона</h5>
            </header>
            <div id="collapse4" class="body">
                <form action = '' method = 'POST'>
                    <div class ="form-group">
                        <label>название</label>
                        <input class ='form-control' type = 'text' name = 'template_name' value = '<?php echo $template['template_name']; ?>' />
                    </div>
                    <div class ="form-group">
                        <label>js</label>
                        <?php
                        foreach ($template['js'] as $key => $js)
                            echo "<input class ='form-control' type = 'text' name = 'js[]' value = '{$js}' /><a href = '" . $this->url->up_to(2) . "edit_file/{$template['name']}/js/{$key}'>Редактировать</a><br /><br />";

                        echo "<input class ='form-control' type = 'text' name = 'js[]' />";
                        ?>
                    </div>
                    <div class ="form-group">
                        <label>css</label>
                        <?php
                        foreach ($template['css'] as $css)
                            echo "<input class ='form-control' type = 'text' name = 'css[]' value = '{$css}' /><a href = '" . $this->url->up_to(2) . "edit_file/{$template['name']}/css/{$key}'>Редактировать</a><br /><br />";

                        echo "<input class ='form-control' type = 'text' name = 'css[]' />";
                        ?>
                    </div>
                    <div class ="form-group">
                        <label>Файл шаблона</label>
                        <?php
                        echo "index.html <a href = '" . $this->url->up_to(2) . "edit_file/{$template['name']}/html/'>Редактировать</a>";
                        ?>
                    </div>
                <!--<input type = 'text' name = 'favicon' value = '<?php echo $template['favicon']; ?>' />-->
                    <button class = 'btn btn-success'>Сохранить</button>
                </form>
            </div>
        </div>
    </div>
</div>