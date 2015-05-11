<form action = "" method = "POST">
    <!--BEGIN INPUT TEXT FIELDS-->
    <div class="row">
        <div class="col-lg-12">
            <div class="box dark">
                <header>
                    <div class="icons">
                        <i class="fa fa-plus"></i>
                    </div>
                    <h5>Создание меню</h5>
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
                        <label for="text1" class="control-label col-lg-2">Алиас</label>
                        <div class="col-lg-10">
                            <input type="text" id="text1" placeholder="" class="form-control" name = 'mg_code' >
                        </div>
                    </div><!-- /.form-group -->
                    <div class="form-group">
                        <label for="text1" class="control-label col-lg-2">Название</label>
                        <div class="col-lg-10">
                            <input type="text" id="text1" placeholder="" class="form-control" name = 'mg_name' >
                        </div>
                    </div><!-- /.form-group -->
                    <input type = 'submit' class = 'btn btn-primary' value = 'Создать' />
                </div>
            </div>
        </div>
    </div>
</form>