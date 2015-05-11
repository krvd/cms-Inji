<form action = "" method = "POST">
    <!--BEGIN INPUT TEXT FIELDS-->
    <div class="row">
        <div class="col-lg-12">
            <div class="box dark">
                <header>
                    <div class="icons">
                        <i class="fa fa-cogs"></i>
                    </div>
                    <h5>Редактирование пункта меню</h5>
                    <!-- .toolbar -->
                    <div class="toolbar">
                        <ul class="nav">
                            <li>
                                <a class="minimize-box" data-toggle="collapse" href="#div-1">
                                    <i class="icon-chevron-up"></i>
                                </a>
                            </li>
                        </ul>
                    </div><!-- /.toolbar -->
                </header>
                <div id="div-1" class="accordion-body collapse in body form-horizontal">
                    <div class="form-group">
                        <label for="text1" class="control-label col-lg-2">Тип</label>
                        <div class="col-lg-10">
                            <select class="form-control" name = 'mi_type' >
                                <option value = 'href' >Ссылка</option>
                                <option value ='Materials' <?php if ($item['mi_type'] == 'Materials') echo'selected'; ?>>Материал</option>
                                <option value ='Form' <?php if ($item['mi_type'] == 'Form') echo'Form'; ?>>Всплывающая форма</option>
                            </select>
                        </div>
                    </div><!-- /.form-group -->

                    <div class="form-group" id = 'Form' style = '<?= empty($item['mi_advance']['Form']) ? 'display:none;' : ''; ?>'>
                        <label for="text1" class="control-label col-lg-2">Форма</label>
                        <div class="col-lg-10">
                            <select class="form-control" name = 'mi_advance[Form]' disabled >
                                <?php
                                $forms = Form::get_list();
                                foreach ($forms as $form) {
                                    $selected = '';
                                    if (!empty($item['mi_advance']['Form']) && $form->form_id == $item['mi_advance']['Form']) {
                                        $selected = 'selected';
                                    }
                                    echo "<option {$selected} value = '{$form->form_id}'>{$form->form_title}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div><!-- /.form-group -->

                    <div class="form-group" id = 'materials' style = '<?= empty($item['mi_advance']['material']) ? 'display:none;' : ''; ?>'>
                        <label for="materials-input" class="control-label col-lg-2">Материал</label>
                        <div class="col-lg-10">
                            <select class="form-control" name = 'mi_advance[material]' disabled data-material_id='<?php if (!empty($item['mi_advance']['material'])) echo $item['mi_advance']['material']; ?>'>
                            </select>
                        </div>
                    </div><!-- /.form-group -->
                    <div class="form-group">
                        <label for="text1" class="control-label col-lg-2">Название</label>
                        <div class="col-lg-10">
                            <input type="text" id="text1" placeholder="" class="form-control" name = 'mi_name' value = '<?php echo $item['mi_name']; ?>' >
                        </div>
                    </div><!-- /.form-group -->
                    <div class="form-group">
                        <label for="text1" class="control-label col-lg-2">Подсказка</label>
                        <div class="col-lg-10">
                            <input type="text" id="text1" placeholder="" class="form-control" name = 'mi_advance[title]' value = '<?php echo (!empty($item['mi_advance']['title'])) ? $item['mi_advance']['title'] : ''; ?>' >
                        </div>
                    </div><!-- /.form-group -->
                    <div id ='link' class="form-group" <?php if ($item['mi_type'] == 'Materials') echo'style="display:none;"'; ?>>
                        <label for="text1" class="control-label col-lg-2">Адрес</label>
                        <div class="col-lg-10">
                            <input type="text" id="text1" placeholder="" class="form-control" name = 'mi_href' value = '<?php echo $item['mi_href']; ?>' >
                        </div>
                    </div><!-- /.form-group -->
                    <input type = 'submit' class = 'btn btn-primary' value = 'Сохранить' />
                </div>
            </div>
        </div>
    </div>
</form>
<script>
    $(function () {
        $('[name = "mi_type"]').change(function () {
            if ($(this).val() == 'Materials') {
                $.getJSON('/admin/Materials/get_list_ajax', function (pages) {
                    for (key in pages) {
                        page = pages[key];
                        $('#materials').show();
                        $('#link').hide();
                        $('#materials select').removeAttr('disabled');
                        option = '<option value = "' + page.material_id + '"';
                        if ($('#materials select').data('material_id') == page.material_id)
                            option += ' selected ';
                        option += '>' + page.material_name + '</option>';
                        $('#materials select').append(option);
                    }
                })
            }
            else if ($(this).val() == 'Form') {
                $('#Form').show();
                $('#Form select').removeAttr('disabled');
                $('#materials').hide();
                $('#link').hide();
                $('#materials select').attr('disabled', true);
            }
            $('#link').show();
            $('#materials').hide();
            $('#materials select').attr('disabled', 'disabled');
            $('#materials select').html('');
        }
        });
        $('[name = "mi_type"]').change();
    })
</script>