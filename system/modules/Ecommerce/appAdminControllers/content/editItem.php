<?php
$LiveForm->view();
exit();
?>
<form action = "" method = "POST" enctype="multipart/form-data">
    <!--BEGIN INPUT TEXT FIELDS-->
    <div class="row">
        <div class="col-lg-<?php echo ($item->catalog->catalog_prices) ? 6 : 12; ?>">
            <?php
            $this->view->helper->start_box('Основные параметры', 'cogs');
            $this->view->helper->form_element('text', array('label' => 'Название',
                'name' => 'ci_name',
                'value' => $item->ci_name
            ));
            $soptions = array();
            if ($item->catalog->catalog_similar) {
                $selected = array();
                if (!empty($item->ci_similar))
                    $sims = explode(',', $item->ci_similar);
                foreach ($items as $item2) {
                    if (empty($item) || $item->ci_id != $item2->ci_id) {
                        if (!empty($sims) && in_array($item2->ci_id, $sims))
                            $selected[] = $item2->ci_id;
                        $soptions[$item2->ci_id] = $item2->ci_name;
                    }
                }
                $this->view->helper->form_element('select', array('label' => 'Похожие товары',
                    'name' => 'ci_similar',
                    'value' => $soptions,
                    'selected' => $selected,
                    'multiple' => true,
                    'class' => 'chzn-select'
                ));
            }
            echo '<div class ="row">';
            foreach ($item->catalog->options as $option) {
                echo "<div class ='col-lg-6'>";
                switch ($option->cio_type) {
                    case 'file':
                        $hover = 'http://www.placehold.it/200x200/EFEFEF/AAAAAA&text=no+image';
                        if (!empty($item->options[$option->cio_code]) && $item->options[$option->cio_code]->file) {
                            $hover = $item->options[$option->cio_code]->file->file_path . '?resize=200x200';
                        }
                        $this->view->helper->form_element('file', array('label' => $option->cio_name,
                            'name' => "option_{$option->cio_id}",
                            'value' => $hover
                        ));
                        break;
                    case 'text':
                        if (!empty($item->options[$option->cio_code]))
                            $value = $item->options[$option->cio_code]->cip_value;
                        else
                            $value = '';
                        $this->view->helper->form_element('text', array('label' => $option->cio_name,
                            'name' => "option_{$option->cio_id}",
                            'value' => $value
                        ));
                        break;
                    case 'date':
                        if (!empty($item->options[$option->cio_code]))
                            $value = $item->options[$option->cio_code]->cip_value;
                        else
                            $value = '';
                        $this->view->helper->form_element('date', array('label' => $option->cio_name,
                            'name' => "option_{$option->cio_id}",
                            'value' => $value
                        ));
                        break;
                    case 'checkbox':
                        if (!empty($item->options[$option->cio_code]))
                            $value = $item->options[$option->cio_code]->cip_value;
                        else
                            $value = '';
                        $this->view->helper->form_element('checkbox', array('label' => $option->cio_name,
                            'name' => "option_{$option->cio_id}",
                            'checked' => $value
                        ));
                        break;
                    case 'select':
                        $soptions = array();
                        $selected = '';
                        $advance = json_decode($option->cio_advance, true);
                        switch ($advance['source']) {
                            case'data':
                                foreach ($advance['data'] as $it) {
                                    if (!empty($item->options[$option->cio_code]) && $item->options[$option->cio_code]->cip_value == $it)
                                        $selected = $it;
                                    $soptions[$it] = $it;
                                }
                                break;
                            case 'catalog':
                                $iitems = $this->ecommerce->getItems($advance['data']);
                                if (!empty($advance['multiple'])) {
                                    $selected = explode(',', $item['params'][$option->cio_id]['cip_value']);
                                } else {
                                    $selected = $item['params'][$option->cio_id]['cip_value'];
                                }
                                foreach ($iitems as $it) {
                                    $soptions[$it['ci_id']] = $it['ci_name'];
                                }
                                break;
                        }
                        $this->view->helper->form_element('select', array('label' => $option->cio_name,
                            'name' => 'option_' . $option->cio_id,
                            'value' => $soptions,
                            'selected' => $selected,
                            'multiple' => (!empty($advance['multiple'])) ? true : false,
                        ));
                        break;
                }

                echo '</div>';
            }
            echo '</div>';
            $this->view->helper->end_box();
            ?>
        </div>
        <?php if ($item->catalog->catalog_prices) { ?>
            <div class="col-lg-6">
                <?php
                $this->view->helper->start_box('Торговые предложения', 'eur', true);
                $ths = array();

                foreach (Inji::app()->ecommerce->modConf['item']['prices'] as $optcol) {
                    $ths[] = $optcol['name'];
                }
                $ths[] = '';
                $rows = array();

                foreach ($item->prices as $key => $price) {
                    $row = array();
                    foreach (Inji::app()->ecommerce->modConf['item']['prices'] as $col => $optcol) {
                        if ($optcol['data']['type'] == 'file') {
                            if ($price->$col) {
                                $file = $this->Files->get($price->$col);
                                $optcol['data']['options']['value'] = $file['file_path'];
                            } else {
                                $optcol['data']['options']['value'] = '';
                            }
                        } else {
                            $optcol['data']['options']['value'] = $price->$col;
                        }
                        $optcol['data']['options']['name'] = str_replace('[]', "[{$price->ciprice_id}]", $optcol['data']['options']['name']);
                        $row[] = $optcol;
                    }
                    $rows[] = $row;
                }

                $this->view->helper->table($ths, $rows, Inji::app()->ecommerce->modConf['item']['prices'], 'inputs');
                $this->view->helper->end_box();
                ?>
            </div>
            <?php
        }

        foreach ($item->catalog->options as $option) {
            switch ($option->cio_type) {
                case 'gallery':
                    ?>
                    <div class="col-lg-6">
                        <div class="box dark">
                            <header>
                                <div class="icons">
                                    <i class="icon-camera"></i>
                                </div>
                                <h5>Галлерея</h5>
                                <!-- .toolbar -->
                                <div class="toolbar">
                                    <ul class="nav">
                                        <li>
                                            <a class="minimize-box" data-toggle="collapse" href="#div-11">
                                                <i class="icon-chevron-up"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div><!-- /.toolbar -->
                            </header>
                            <div id="div-11" class="accordion-body collapse in body form-horizontal">


                                <div id="uploader">
                                    <p>Your browser doesn't have Flash, Silverlight or HTML5 support.</p>
                                </div>

                                <link rel="stylesheet" href="/admin/static/plupload/jquery.ui.plupload/css/jquery.ui.plupload.css" type="text/css" />
                                <script type="text/javascript" src="/admin/static/plupload/plupload.full.min.js"></script>
                                <script type="text/javascript" src="/admin/static/plupload/jquery.ui.plupload/jquery.ui.plupload.js"></script>
                                <script type="text/javascript">
                                    // Convert divs to queue widgets when the DOM is ready
                                    $(function () {

                                        $("#uploader").plupload({
                                            // General settings
                                            runtimes: 'html5,flash,silverlight,html4',
                                            url: '<?php echo $this->url->module(); ?>/upload_images',
                                            file_data_name: 'uploaded_file',
                                            //chunk_size : '1mb',
                                            //unique_names : true,

                                            // Resize images on clientside if we can
                                            //resize : {width : 320, height : 240, quality : 90},

                                            // Specify what files to browse for
                                            filters: {
                                                max_file_size: '100mb',
                                                mime_types: [
                                                    {title: "Image files", extensions: "jpg,jpeg,gif,png"}//,
                                                    //{title : "Zip files", extensions : "zip"}
                                                ]
                                            },
                                            // Flash settings
                                            flash_swf_url: '/admin/static/plupload/Moxie.swf',
                                            // Silverlight settings
                                            silverlight_xap_url: '/admin/static/plupload/Moxie.xap',
                                            // PreInit events, bound before any internal events
                                            preinit: {
                                                Init: function (up, info) {
                                                    //log('[Init]', 'Info:', info, 'Features:', up.features);
                                                },
                                                UploadFile: function (up, file) {
                                                    //log('[UploadFile]', file);

                                                    // You can override settings before the file is uploaded
                                                    // up.settings.url = 'upload.php?id=' + file.id;
                                                    // up.settings.multipart_params = {param1 : 'value1', param2 : 'value2'};
                                                }
                                            },
                                            // Post init events, bound after the internal events
                                            init: {
                                                Refresh: function (up) {
                                                    // Called when upload shim is moved
                                                    //log('[Refresh]');
                                                },
                                                StateChanged: function (up) {
                                                    // Called when the state of the queue is changed
                                                    //log('[StateChanged]', up.state == plupload.STARTED ? "STARTED" : "STOPPED");
                                                },
                                                QueueChanged: function (up) {
                                                    // Called when the files in queue are changed by adding/removing files
                                                    //log('[QueueChanged]');
                                                },
                                                UploadProgress: function (up, file) {
                                                    // Called while a file is being uploaded
                                                    //log('[UploadProgress]', 'File:', file, "Total:", up.total);
                                                },
                                                FilesAdded: function (up, files) {
                                                    // Callced when files are added to queue
                                                    //log('[FilesAdded]');

                                                    plupload.each(files, function (file) {
                                                        //log('  File:', file);
                                                    });
                                                },
                                                FilesRemoved: function (up, files) {
                                                    // Called when files where removed from queue
                                                    //log('[FilesRemoved]');

                                                    plupload.each(files, function (file) {
                                                        //log('  File:', file);
                                                    });
                                                },
                                                FileUploaded: function (up, file, info) {
                                                    // Called when a file has finished uploading
                                                    console.log('[FileUploaded] File:', file, "Info:", $.parseJSON(info.response));
                                                    data = jQuery.parseJSON(info.response);
                                                    $('#gimages tbody').append("<tr><td><input type = 'hidden' name = 'option_<?php echo $option->cio_id; ?>[file_id][" + data.file_id + "]' value = '" + data.file_id + "'/>" + data.file_id + "</td>" +
                                                            "<td><img src = '" + data.file_path + "?resize=50x50&resize_quadro=1'/></td>" +
                                                            "<td><input type = 'text' name = 'option_<?php echo $option->cio_id; ?>[file_name][" + data.file_id + "]' value = '" + data.file_name + "'/></td>" +
                                                            "<td><textarea rows = 2  name = 'option_<?php echo $option->cio_id; ?>[file_about][" + data.file_id + "]' >" + data.file_about + "</textarea></td>" +
                                                            "<td class = 'gtoolbar' file_id = '" + data.file_id + "' style = 'text-align:center;cursor:pointer;'>удалить</td></tr>");
                                                    $('.gtoolbar').unbind('click');
                                                    $('.sortable').unbind('sortable');
                                                    $('.gtoolbar').on('click', function () {
                                                        data = [];
                                                        data['data'] = [];
                                                        data['data']['id'] = $(this).next().next().val();
                                                        data['url'] = '<?php echo $this->url->up_to(2, "delete_image"); ?>/' + $(this).attr('file_id')
                                                        $.ajax(data);
                                                        $(this).parent().remove();
                                                    });
                                                    $(".sortable").sortable({
                                                        placeholder: "ui-state-highlight"
                                                    });
                                                    //$( ".sortable" ).disableSelection();
                                                },
                                                ChunkUploaded: function (up, file, info) {
                                                    // Called when a file chunk has finished uploading
                                                    //log('[ChunkUploaded] File:', file, "Info:", info);
                                                },
                                                Error: function (up, args) {
                                                    // Called when a error has occured
                                                    //log('[error] ', args);
                                                }
                                            }
                                        });
                                    });
                                </script>
                                <style>
                                    .sortable td {
                                        cursor: move
                                    }
                                    .ui-state-highlight { height: 81px;}
                                </style>
                                <div class="box">
                                    <header>
                                        <h5>Загруженные изображения</h5>
                                        <div class="toolbar">
                                            <div class="btn-group">
                                                <a href="#stripedTable" data-toggle="collapse" class="btn btn-default btn-sm minimize-box">
                                                    <i class="icon-angle-up"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </header>
                                    <div id="stripedTable" class="body collapse in">
                                        <table class="table table-striped responsive-table" id = 'gimages'>
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Превью</th>
                                                    <th>Название</th>
                                                    <th>Описание</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody class = 'sortable'>
                                                <?php
                                                if (!empty($item))
                                                    $photos = explode(',', $item['params'][$option->cio_id]['cip_value']);
                                                else
                                                    $photos = [];
                                                foreach ($photos as $photo_id) {
                                                    if ($photo_id) {
                                                        $photo = $this->Files->get($photo_id);
                                                        echo "<tr><td><input type = 'hidden' name = 'option_{$option->cio_id}[file_id][" . $photo['file_id'] . "]' value = '" . $photo['file_id'] . "'/>" . $photo['file_id'] . "</td>";
                                                        echo "<td><img src = '" . $photo['file_path'] . "?resize=50x50&resize_quadro=1'/></td>";
                                                        echo "<td><input type = 'text' name = 'option_{$option->cio_id}[file_name][" . $photo['file_id'] . "]' value = '" . $photo['file_name'] . "'/></td>";
                                                        echo "<td><textarea rows = 2  name = 'option_{$option->cio_id}[file_about][" . $photo['file_id'] . "]' >" . $photo['file_about'] . "</textarea></td>";
                                                        echo "<td class = 'gtoolbar' file_id = '" . $photo['file_id'] . "' style = 'text-align:center;cursor:pointer;'>удалить</td></tr>";
                                                    }
                                                }
                                                ?>


                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <script>
                                    $('.gtoolbar').unbind('click');
                                    $('.sortable').unbind('sortable');
                                    $('.gtoolbar').on('click', function () {
                                        data = [];
                                        data['data'] = [];
                                        data['data']['id'] = $(this).next().next().val();
                                        data['url'] = '<?php echo $this->url->up_to(2, "delete_image"); ?>/' + $(this).attr('file_id')
                                        $.ajax(data);
                                        $(this).parent().remove();
                                    });
                                    $(".sortable").sortable({
                                        placeholder: "ui-state-highlight"
                                    });
                                    //$( ".sortable" ).disableSelection();
                                </script>

                            </div>
                        </div>
                    </div>
                    <?php
                    break;
            }
        }
        ?>
    </div>
    <input type = 'submit' class = 'btn btn-primary' value = 'Сохранить' />
    <!--END TEXT INPUT FIELD-->
    <?php
    $save = false;

    foreach ($item->catalog->options as $option) {
        switch ($option->cio_type) {
            case 'textarea':
                $save = true;
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="box">
                            <header>
                                <div class="icons">
                                    <i class="icon-edit"></i>
                                </div>
                                <h5><?php echo $option->cio_name; ?></h5>
                                <!-- .toolbar -->
                                <div class="toolbar">
                                    <ul class="nav">
                                        <li>
                                            <a class="minimize-box" data-toggle="collapse" href="#div-2">
                                                <i class="icon-chevron-up"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div><!-- /.toolbar -->
                            </header>
                            <div id="div-2" class="body collapse in">
                                <textarea class='ckeditor' id = 'param_<?php echo $option->cio_id; ?>' name = 'option_<?php echo $option->cio_id; ?>' style = 'width:100%;height:30em;'><?php if (!empty($item->options[$option->cio_code])) echo $item->options[$option->cio_code]->cip_value; ?></textarea>
                            </div>
                        </div>
                    </div><!-- /.col-lg-12 -->
                </div>
                <?php
                break;
        }
    }
    if ($save) {
        ?>
        <input type = 'submit' class = 'btn btn-primary' value = 'Сохранить' />
    <?php } ?>
</form>
<script type="text/javascript" src="/admin/static/ckeditor/ckeditor.js"></script>
