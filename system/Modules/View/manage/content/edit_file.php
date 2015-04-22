<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <header>
                <div class="icons">
                    <i class="fa fa-table"></i>
                </div>
                <h5>редактирование файла</h5>
            </header>
            <div id="collapse4" class="body">
                <form action = '' method = 'POST'>
                    <div class ="form-group">
                        <textarea data-editor ='<?php
                        switch ($type) {
                            case'js':
                                echo 'javascript';
                                break;
                            default:
                                echo $type;
                        }
                        ?>' name ='text' id = 'editor' class = 'form-control' style = 'width: 100%;
                                  height:500px; '><?php echo htmlspecialchars($text); ?></textarea>
                        <script>
                            // Hook up ACE editor to all textareas with data-editor attribute
                            $(function() {
                                $('textarea[data-editor]').each(function() {
                                    var textarea = $(this);

                                    var mode = textarea.data('editor');

                                    var editDiv = $('<div>', {
                                        position: 'absolute',
                                        width: textarea.width(),
                                        height: textarea.height(),
                                        'class': textarea.attr('class')
                                    }).insertBefore(textarea);

                                    textarea.css('visibility', 'hidden');

                                    var editor = ace.edit(editDiv[0]);
                                    editor.renderer.setShowGutter(true);
                                    editor.setTheme("ace/theme/dawn");
                                    editor.getSession().setValue(textarea.val());
                                    editor.getSession().setMode("ace/mode/" + mode);
                                    // editor.setTheme("ace/theme/idle_fingers");
                                    // copy back to textarea on form submit...
                                    textarea.closest('form').submit(function() {
                                        textarea.val(editor.getSession().getValue());
                                    })
                                    textarea.css('height', 0);
                                });
                            });
                        </script>
                    </div>
                    <div class ="form-group">
                        <button class = 'btn btn-success'>Изменить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>