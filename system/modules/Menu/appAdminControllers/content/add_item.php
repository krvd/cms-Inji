<form action = "" method = "POST">
    <!--BEGIN INPUT TEXT FIELDS-->
    <div class="row">
        <div class="col-lg-12">
            <div class="box dark">
                <header>
                    <div class="icons">
                        <i class="fa fa-plus"></i>
                    </div>
                    <h5>Добавление элемента меню</h5>
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
                                <option value = 'href'>Ссылка</option>
                                <option value ='Materials'>Материал</option>
                                <option value ='Form'>Всплывающая форма</option>
                            </select>
                        </div>
                    </div><!-- /.form-group -->

                    <div class="form-group" id = 'Form' style = 'display:none;'>
                        <label for="text1" class="control-label col-lg-2">Форма</label>
                        <div class="col-lg-10">
                            <select class="form-control" name = 'mi_advance[Form]' disabled >
                                <?php
                                $forms = Form::get_list();
                                foreach ($forms as $form){
                                    echo "<option value = '{$form->form_id}'>{$form->form_title}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div><!-- /.form-group -->

                    <div class="form-group" id = 'materials' style = 'display:none;'>
                        <label for="text1" class="control-label col-lg-2">Материал</label>
                        <div class="col-lg-10">
                            <select class="form-control" name = 'mi_advance[material]' disabled >
                            </select>
                        </div>
                    </div><!-- /.form-group -->

                    <div class="form-group">
                        <label for="text1" class="control-label col-lg-2">Название</label>
                        <div class="col-lg-10">
                            <input type="text" id="text1" placeholder="" class="form-control" name = 'mi_name' >
                        </div>
                    </div><!-- /.form-group -->
                    <div class="form-group">
                        <label for="text1" class="control-label col-lg-2">Подсказка</label>
                        <div class="col-lg-10">
                            <input type="text" id="text1" placeholder="" class="form-control" name = 'mi_advance[title]' >
                        </div>
                    </div><!-- /.form-group -->

                    <div class="form-group" id = 'link'>
                        <label for="text1" class="control-label col-lg-2">Ссылка</label>
                        <div class="col-lg-10">
                            <input type="text" id="text1" placeholder="" class="form-control" name = 'mi_href' >
                        </div>
                    </div><!-- /.form-group -->
                    <input type = 'submit' class = 'btn btn-primary' value = 'Добавить' />
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
                        $('#Form').hide();
                        $('#Form select').attr('disabled', true);
                        $('#materials select').removeAttr('disabled');
                        $('#materials select').append('<option value = "' + page.material_id + '">' + page.material_name + '</option>');
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
            else {
                $('#link').show();
                $('#Form').hide();
                $('#Form select').attr('disabled', true);
                $('#materials').hide();
                $('#materials select').attr('disabled', 'disabled');
                $('#materials select').html('');
            }
        })
    })
</script>