<form action = "" method = "POST" enctype="multipart/form-data">
    <!--BEGIN INPUT TEXT FIELDS-->
    <div class="row">
        <div class="col-md-5">
            <?php
            $this->helper->start_box('Основные параметры', 'cogs');
            $this->helper->form_element('text', array('label' => 'Название раздела',
                'name' => 'catalog_name',
                'value' => $catalog->catalog_name
            ));
            if ($catalog->image) {
                $image = $catalog->image->file_path;
            } else
                $image = '';

            $this->helper->form_element('image', array('label' => 'Обложка раздела',
                'name' => 'catalog_image',
                'value' => $image
            ));
            $soptions = array();
            foreach ($catalogs as $parent) {
                $soptions[$parent->catalog_id] = $parent->catalog_name;
            }
            $this->helper->form_element('select', array('label' => 'Родительский раздел',
                'name' => 'catalog_parent_id',
                'value' => $soptions,
                'selected' => $catalog->catalog_parent_id,
            ));
            $this->helper->form_element('checkbox', array('label' => 'Торговые предложения',
                'name' => "catalog_prices",
                'value' => $catalog->catalog_prices,
            ));
            $this->helper->form_element('checkbox', array('label' => 'Похожие товары',
                'name' => "catalog_similar",
                'value' => $catalog->catalog_similar
            ));
            $this->helper->end_box();
            ?>
        </div>
        <div class="col-md-7">
            <div class="box dark">
                <header>
                    <div class="icons">
                        <i class="fa fa-list"></i>
                    </div>
                    <h5>Параметры товаров в разделе</h5>
                    <!-- .toolbar -->
                    <div class="toolbar">
                        <div class="btn-group">		  
                            <a href ='#option_add' class="btn btn-success btn-sm addoption"  data-toggle="modal"><i class="fa fa-plus"></i></a>
                            <a href="#div-options" data-toggle="collapse" class="btn btn-default btn-sm accordion-toggle minimize-box" >
                                <i class="fa fa-chevron-up"></i>
                            </a>
                        </div>
                    </div>
                    <!-- /.toolbar -->
                </header>
                <div id="div-options" class="body collapse in">
                    <table class="table table-striped responsive-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Название</th>
                                <th>Тип</th>
                                <th>Настройки</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class = 'sortable'>
                            <?php
                            if ($catalog->options) {
                                foreach ($catalog->options as $option) {
                                    ?>
                                    <tr class = 'row<?php echo $option->cio_id; ?>'>
                                        <td>
                                            <input type = 'hidden' name = 'exis_options[<?php echo $option->cio_id; ?>][cio_id]' value = '<?php echo $option->cio_id; ?>' /><?php echo $option->cio_id; ?>
                                        </td>
                                        <td>
                                            <input class="form-control" type = 'text' name = 'exis_options[<?php echo $option->cio_id; ?>][cio_name]' value = '<?php echo $option->cio_name; ?>' />
                                            <input type = 'hidden' name = 'exis_options[<?php echo $option->cio_id; ?>][cio_code]' class = 'cio_code<?php echo $option->cio_id; ?>' value = '<?php echo $option->cio_code; ?>' />
                                            <input type = 'hidden' name = 'exis_options[<?php echo $option->cio_id; ?>][cio_default_val]' class = 'cio_default_val<?php echo $option->cio_id; ?>' value = '<?php echo $option->cio_default_val; ?>' />
                                        </td>
                                        <td><select name = 'exis_options[<?php echo $option->cio_id; ?>][cio_type]' class="cio_type cio_type<?php echo $option->cio_id; ?> form-control"><?php
                                                $types = array();
                                                $types['text'] = 'Однострочный текст';
                                                $types['textarea'] = 'Многострочный текст';
                                                $types['checkbox'] = 'Галочка';
                                                $types['date'] = 'Дата';
                                                $types['select'] = 'Список';
                                                $types['file'] = 'Фото';
                                                $types['gallery'] = 'Фотогаллерея';
                                                foreach ($types as $code => $type) {
                                                    if ($option->cio_type == $code)
                                                        $selected = 'selected="selected"';
                                                    else
                                                        $selected = '';
                                                    echo "<option {$selected} value = '{$code}'>{$type}</option>";
                                                }
                                                ?></select></td>
                                        <td><input type = 'hidden' name = 'exis_options[<?php echo $option->cio_id; ?>][cio_advance]' class = 'option_advance_value<?php echo $option->cio_id; ?>' value = '<?php echo $option->cio_advance; ?>' />
                                            <a href ='#option_advance' cioid = '<?php echo $option->cio_id; ?>' class="btn btn-info btn-sm option_advance" data-toggle="modal">Настроить</a>
                                        </td>
                                        <td><a class="btn btn-danger btn-sm delete delproove" cl = 'row<?php echo $option->cio_id; ?>' cio_id = "<?php echo $option->cio_id; ?>"><i class="fa fa-times"></i></a></td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                    <script>
                        var newcount = 1;
                        var catalogs = <?php echo json_encode($catalogs); ?>;
                        $(function () {
                            $(".sortable").sortable({
                                placeholder: "ui-state-highlight"
                            });
                            $('.sortable').on('change', '.cio_type', function () {
                                //alert(1);
                            });
                            $('.importoptions').click(function () {
                                $('#option_import').modal('hide');
                                data = {};
                                data.url = '/admin/Ecommerce/get_catalog_options_ajax/' + $('#option_import select option:selected').val();
                                data.dataType = 'json';
                                data.success = function (data) {
                                    for (key in data) {
                                        option = data[key];
                                        if (typeof (option.cio_id) != 'undefined' && !$('.row' + option.cio_id).length) {
                                            $('#option_add select option:selected').attr('disabled', 'disabled');
                                            $('#option_add select option:selected').removeAttr('selected');
                                            types = {
                                                'text': 'Однострочный текст',
                                                'textarea': 'Многострочный текст',
                                                'checkbox': 'Галочка',
                                                'date': 'Дата',
                                                'select': 'Список',
                                                'file': 'Фото',
                                                'gallery': 'Фотогаллерея',
                                            };
                                            html = '<tr class = "row' + option.cio_id + '"><td>' +
                                                    '<input type = "hidden" name = "exis_options[' + option.cio_id + '][cio_id]" value = "' + option.cio_id + '" />' + option.cio_id +
                                                    '</td>' +
                                                    '<td>' +
                                                    '<input class="form-control" type = "text" name = "exis_options[' + option.cio_id + '][cio_name]" value = "' + option.cio_name + '" />' +
                                                    '<input type = "hidden" name = "exis_options[' + option.cio_id + '][cio_code]" class = "cio_code' + option.cio_id + '" value = "' + option.cio_code + '" />' +
                                                    '<input type = "hidden" name = "exis_options[' + option.cio_id + '][cio_default_val]" class = "cio_default_val' + option.cio_id + '" value = "' + option.cio_default_val + '" />' +
                                                    '</td>' +
                                                    '<td><select name = "exis_options[' + option.cio_id + '][cio_type]" class="cio_type cio_type' + option.cio_id + ' form-control">';
                                            for (key in types) {
                                                if (option.cio_type == key)
                                                    selected = 'selected="selected"';
                                                else
                                                    selected = '';
                                                html += "<option " + selected + " value = \"" + key + "\">" + types[key] + "</option>";
                                            }

                                            html += '</select></td>' +
                                                    '<td><input type = "hidden" name = "exis_options[' + option.cio_id + '][cio_advance]" class = "option_advance_value' + option.cio_id + '" value = "' + option.cio_advance + '" />' +
                                                    '<a href ="#option_advance" cioid = "' + option.cio_id + '" class="btn btn-info btn-sm option_advance" data-toggle="modal">Настроить</a>' +
                                                    '</td>' +
                                                    '<td><a class="btn btn-danger btn-sm delete delproove" cl = "row' + option.cio_id + '" cio_id = "' + option.cio_id + '"><i class="fa fa-times"></i></a></td>' +
                                                    '</tr>';
                                            $(".sortable").append(html);
                                        }
                                    }
                                }
                                $.ajax(data);
                            });
                            $('.addextoption').click(function () {
                                option = $.parseJSON($('#option_add select option:selected').val());
                                if (typeof (option.cio_id) != 'undefined') {
                                    $('#option_add select option:selected').attr('disabled', 'disabled');
                                    $('#option_add select option:selected').removeAttr('selected');
                                    types = {
                                        'text': 'Однострочный текст',
                                        'textarea': 'Многострочный текст',
                                        'checkbox': 'Галочка',
                                        'date': 'Дата',
                                        'select': 'Список',
                                        'file': 'Фото',
                                        'gallery': 'Фотогаллерея',
                                    };
                                    html = '<tr class = "row' + option.cio_id + '"><td>' +
                                            '<input type = "hidden" name = "exis_options[' + option.cio_id + '][cio_id]" value = "' + option.cio_id + '" />' + option.cio_id +
                                            '</td>' +
                                            '<td>' +
                                            '<input class="form-control" type = "text" name = "exis_options[' + option.cio_id + '][cio_name]" value = "' + option.cio_name + '" />' +
                                            '<input type = "hidden" name = "exis_options[' + option.cio_id + '][cio_code]" class = "cio_code' + option.cio_id + '" value = "' + option.cio_code + '" />' +
                                            '<input type = "hidden" name = "exis_options[' + option.cio_id + '][cio_default_val]" class = "cio_default_val' + option.cio_id + '" value = "' + option.cio_default_val + '" />' +
                                            '</td>' +
                                            '<td><select name = "exis_options[' + option.cio_id + '][cio_type]" class="cio_type cio_type' + option.cio_id + ' form-control">';
                                    for (key in types) {
                                        if (option.cio_type == key)
                                            selected = 'selected="selected"';
                                        else
                                            selected = '';
                                        html += "<option " + selected + " value = \"" + key + "\">" + types[key] + "</option>";
                                    }

                                    html += '</select></td>' +
                                            '<td><input type = "hidden" name = "exis_options[' + option.cio_id + '][cio_advance]" class = "option_advance_value' + option.cio_id + '" value = "' + option.cio_advance + '" />' +
                                            '<a href ="#option_advance" cioid = "' + option.cio_id + '" class="btn btn-info btn-sm option_advance" data-toggle="modal">Настроить</a>' +
                                            '</td>' +
                                            '<td><a class="btn btn-danger btn-sm delete delproove" cl = "row' + option.cio_id + '" cio_id = "' + option.cio_id + '"><i class="fa fa-times"></i></a></td>' +
                                            '</tr>';
                                    $(".sortable").append(html);
                                }
                            });
                            $('.addnewoption').click(function () {

                                $(".sortable").append('<tr class = "rownew' + newcount + '"><td>' +
                                        '<input type = "hidden" name = "exis_options[new' + newcount + '][cio_id]" value = "new' + newcount + '" />new' + newcount +
                                        '</td>' +
                                        '<td>' +
                                        '<input class="form-control" type = "text" name = "exis_options[new' + newcount + '][cio_name]" value = "" />' +
                                        '<input type = "hidden" name = "exis_options[new' + newcount + '][cio_code]" class = "cio_codenew' + newcount + '" value = "" />' +
                                        '<input type = "hidden" name = "exis_options[new' + newcount + '][cio_default_val]" class = "cio_default_valnew' + newcount + '" value = "" />' +
                                        '</td>' +
                                        '<td><select name = "exis_options[new' + newcount + '][cio_type]" class="cio_type cio_typenew' + newcount + ' form-control"><?php
                            $types = array();
                            $types['text'] = 'Однострочный текст';
                            $types['textarea'] = 'Многострочный текст';
                            $types['checkbox'] = 'Галочка';
                            $types['date'] = 'Дата';
                            $types['select'] = 'Список';
                            $types['file'] = 'Фото';
                            $types['gallery'] = 'Фотогаллерея';
                            foreach ($types as $code => $type) {
                                echo "<option value = \"{$code}\">{$type}</option>";
                            }
                            ?></select></td>' +
                                        '<td><input type = "hidden" name = "exis_options[new' + newcount + '][cio_advance]" class = "option_advance_valuenew' + newcount + '" value = "{}" />' +
                                        '<a href ="#option_advance" cioid = "new' + newcount + '" class="btn btn-info btn-sm option_advance" data-toggle="modal">Настроить</a>' +
                                        '</td>' +
                                        '<td><a class="btn btn-danger btn-sm delete delproove" cl = "rownew' + newcount + '"><i class="fa fa-times"></i></a></td>' +
                                        '</tr>');
                                newcount++;
                            });

                            $('.sortable').on('click', '.option_advance', function () {
                                jso = $('.option_advance_value' + $(this).attr('cioid')).val();
                                data = '';
                                if (jso != '' && jso != '{') {
                                    data = $.parseJSON(jso);
                                }
                                else {
                                    data = {
                                        'source': 'data',
                                        'data': {}
                                    };
                                }
                                text = '<table class="table table-striped responsive-table" ><tbody>';
                                text += '<tr><td>Код опции</td><td><input class="form-control optioncode" type = "text" value = "' + $('.cio_code' + $(this).attr('cioid')).val() + '" /></td></tr>';
                                switch ($('.cio_type' + $(this).attr('cioid')).val()) {
                                    case 'select':
                                        text += '<tr><td>Источник</td><td><select class = "form-control optisource"><option value = "data">Данные</option><option value = "catalog"';
                                        if (data.source == 'catalog')
                                            text += ' selected';
                                        text += '>Раздел каталога</option></select></td></tr>';
                                        text += '<tr><td>По умолчанию</td><td><select class = "form-control optidef"><option value = "">Не выбрано</option></select></td></tr>';
                                        break;
                                }
                                text += '</tbody></table><div class ="dinopt"></div>';
                                $('#option_advance .modal-body').html(text);
                                $('.optisource').change(function () {
                                    text = '';
                                    switch ($('.optisource :selected').val()) {
                                        case 'data':
                                            text += '<p>Укажите возможные элементы списка</p><table class="table table-striped responsive-table selectoptions" ><tbody>';
                                            if (data) {
                                                for (key in data.data) {
                                                    if ($('.cio_default_val' + $(this).attr('cioid')).val() == data.data[key])
                                                        selected = 'selected="seleted"';
                                                    else
                                                        selected = '';
                                                    text += '<tr><td><input class="form-control" value ="' + data.data[key] + '" /></td><td><a class="btn btn-danger btn-sm delete delproove"><i class="fa fa-times"></i></a></td></tr>';
                                                    $('.optidef').append('<option ' + selected + ' value = "' + data.data[key] + '">' + data.data[key] + '</option>');
                                                }
                                            }
                                            $('#option_advance .modal-body .dinopt').html(text + '</tbody></table><button type="button" class="btn btn-success addrowinmodal">Добавить строчку</button>');
                                            $('.addrowinmodal').click(function () {
                                                $('#option_advance .modal-body .selectoptions tbody').append('<tr><td><input class="form-control" value ="" /></td><td><a class="btn btn-danger btn-sm delete delproove"><i class="fa fa-times"></i></a></td></tr>');
                                                $('.optidef').append('<option value = ""></option>');
                                            });
                                            $('#option_advance').on('keyup', '.selectoptions input', function () {
                                                $($('.optidef option').get($(this).parent().parent().index() + 1)).text($(this).val());
                                                $($('.optidef option').get($(this).parent().parent().index() + 1)).val($(this).val());
                                            });
                                            break;
                                        case 'catalog':
                                            text += '<select class = "form-control catalogsel">';
                                            for (catalog_id in catalogs) {
                                                text += '<option value = "' + catalog_id + '">' + catalogs[catalog_id].catalog_name + '</option>';
                                            }
                                            text += '</select>';
                                            $('#option_advance .modal-body .dinopt').html(text);
                                            break;
                                    }
                                })
                                $('#option_advance .btn-success').attr('cioid', $(this).attr('cioid'));

                                switch ($('.cio_type' + $(this).attr('cioid')).val()) {
                                    case 'select':
                                        text = '';
                                        switch (data.source) {
                                            case 'data':
                                                text += '<p>Укажите возможные элементы списка</p><table class="table table-striped responsive-table selectoptions" ><tbody>';
                                                if (data) {
                                                    for (key in data.data) {
                                                        if ($('.cio_default_val' + $(this).attr('cioid')).val() == data.data[key])
                                                            selected = 'selected="seleted"';
                                                        else
                                                            selected = '';
                                                        text += '<tr><td><input class="form-control" value ="' + data.data[key] + '" /></td><td><a class="btn btn-danger btn-sm delete delproove"><i class="fa fa-times"></i></a></td></tr>';
                                                        $('.optidef').append('<option ' + selected + ' value = "' + data.data[key] + '">' + data.data[key] + '</option>');
                                                    }
                                                }
                                                $('#option_advance .modal-body .dinopt').html(text + '</tbody></table><button type="button" class="btn btn-success addrowinmodal">Добавить строчку</button>');
                                                $('.addrowinmodal').click(function () {
                                                    $('#option_advance .modal-body .selectoptions tbody').append('<tr><td><input class="form-control" value ="" /></td><td><a class="btn btn-danger btn-sm delete delproove"><i class="fa fa-times"></i></a></td></tr>');
                                                    $('.optidef').append('<option value = ""></option>');
                                                });
                                                $('#option_advance').on('keyup', '.selectoptions input', function () {
                                                    $($('.optidef option').get($(this).parent().parent().index() + 1)).text($(this).val());
                                                    $($('.optidef option').get($(this).parent().parent().index() + 1)).val($(this).val());
                                                });
                                                break;
                                            case 'catalog':
                                                text += '<select class = "form-control catalogsel">';
                                                for (catalog_id in catalogs) {
                                                    text += '<option value = "' + catalog_id + '"';
                                                    if (data.data == catalog_id)
                                                        text += ' selected';

                                                    text += '>' + catalogs[catalog_id].catalog_name + '</option>';
                                                }
                                                text += '</select>';
                                                $('#option_advance .modal-body .dinopt').html(text);
                                                break;
                                        }
                                        break;
                                }
                            });
                            $('.sortable').on('click', '.delete', function () {
                                $('.' + $(this).attr('cl')).remove();
                                $('#option_add select option[cio_id="' + $(this).attr('cio_id') + '"]').removeAttr('disabled');
                            });
                            $('#option_advance .modal-body').on('click', '.delete', function () {
                                $($('.optidef option').get($(this).parent().parent().index() + 1)).remove();
                                $(this).parent().parent().remove();
                            });
                            $('#option_advance .modal-footer .btn-success').click(function () {
                                cioid = $(this).attr('cioid');
                                $('.cio_code' + cioid).val($('#option_advance .modal-body .optioncode').val());
                                $('.cio_default_val' + cioid).val($('#option_advance .modal-body .optidef').val());
                                data = {};
                                switch ($('.cio_type' + cioid).val()) {
                                    case 'select':
                                        switch ($('.optisource :selected').val()) {
                                            case 'data':
                                                data['source'] = 'data';
                                                data['data'] = {};
                                                i = 0;
                                                $('#option_advance .modal-body .selectoptions tr').each(function () {
                                                    data['data'][i++] = $(this).find('input').val();
                                                });
                                                $('.option_advance_value' + $(this).attr('cioid')).val(JSON.stringify(data));
                                                break;
                                            case 'catalog':
                                                data['source'] = 'catalog';
                                                data['data'] = $('.catalogsel :selected').val();
                                                $('.option_advance_value' + $(this).attr('cioid')).val(JSON.stringify(data));
                                                break;
                                        }
                                        break;
                                }
                            });
                        });</script>
                    <style>
                        .sortable td {
                            cursor: move
                        }

                    </style>
                </div>
                <!-- #helpModal -->        
                <div id="option_advance" class="modal fade">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                <h4 class="modal-title">Дополнительные настройки параметра</h4>
                            </div>
                            <div class="modal-body">
                                <p>
                                    Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                                </p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-success" data-dismiss="modal">Сохранить</button>
                                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->        
                <!-- /#helpModal -->
                <!-- #helpModal -->        
                <div id="option_add" class="modal fade">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                <h4 class="modal-title">Добавление опции</h4>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-lg-8">
                                        <div class="form-group">
                                            <select class = 'form-control'>
                                                <option value = '{}' >Выберите опцию</option>
                                                <?php
                                                foreach ($all_options as $option) {
                                                    $disabled = '';
                                                    if (isset($options[$option->cio_id]))
                                                        $disabled = 'disabled';
                                                    echo "<option value = '" . json_encode($option->params) . "' cio_id = '{$option->cio_id}' {$disabled}>{$option->cio_name}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <a type="button" class="btn btn-success addextoption"  data-dismiss="modal">Добавить</a>
                                    </div>
                                </div>
                                <div class="text-center ">
                                    <a type="button" class="btn btn-primary addfromcatalog" data-dismiss="modal" href ='#option_import' data-toggle="modal">Импорт опций из другого каталога</a>
                                    <a type="button" class="btn btn-primary addnewoption" data-dismiss="modal" >Добавить новую</a>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->        
                <!-- /#helpModal -->
                <!-- /#helpModal -->
                <!-- #helpModal -->        
                <div id="option_import" class="modal fade">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                <h4 class="modal-title">Импорт опций</h4>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-lg-8">
                                        <div class="form-group">
                                            <select class = 'form-control'>
                                                <option value = '{}' >Выберите каталог</option>
                                                <?php
                                                foreach ($catalogs as $parent) {
                                                    if ($catalog->catalog_id == $parent->catalog_id)
                                                        continue;
                                                    echo "<option value = '{$parent->catalog_id}' {$selected}>{$parent->catalog_name}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <a type="button" class="btn btn-success importoptions"  data-dismiss="modal" >Импорт</a>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->        
                <!-- /#helpModal -->
            </div>
        </div>
    </div>
    <input type = 'submit' class = 'btn btn-primary' value = 'Сохранить' />
    <!--END TEXT INPUT FIELD-->
    <div class="row">
        <div class="col-lg-12">
            <div class="box">
                <header>
                    <div class="icons">
                        <i class="fa fa-edit"></i>
                    </div>
                    <h5>Описание раздела</h5>
                    <!-- .toolbar -->
                    <div class="toolbar">
                        <ul class="nav">
                            <li>
                                <a class="minimize-box" data-toggle="collapse" href="#div-catalog_description">
                                    <i class="fa fa-chevron-up"></i>
                                </a>
                            </li>
                        </ul>
                    </div><!-- /.toolbar -->
                </header>
                <div id="div-catalog_description" class="body collapse in">
                    <textarea class='ckeditor' id = 'catalog_description' name = 'catalog_description' style = 'width:100%;height:30em;'><?php if (!empty($catalog->catalog_description)) echo $catalog->catalog_description; ?></textarea>
                </div>
            </div>
        </div><!-- /.col-lg-12 -->
    </div>
    <input type = 'submit' class = 'btn btn-primary' value = 'Сохранить' />
</form>
<script type="text/javascript" src="/admin/static/ckeditor/ckeditor.js"></script>