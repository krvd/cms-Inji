function Ui() {
    this.modal = null;
    this.dataManager = null;
}
Modal = function () {
    this.modals = 0;
}
Modal.prototype.show = function (title, body, code, size) {
    if (code == null) {
        code = 'modal' + (++this.modals);
    }
    if (size == null) {
        size = '';
    }
    if (title) {
        title = '<div class="modal-header">\
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>\
                  <h4 class="modal-title">' + title + '</h4>\
                </div>';
    }
    else {
        title = '';
    }
    var html = '\
          <div class="modal fade" id = "' + code + '" >\
            <div class="modal-dialog ' + size + '">\
              <div class="modal-content">\
                ' + title + '\
                <div class="modal-body">\
                ' + body + '\
                </div>\
                <div class="modal-footer">\
                  <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>\
                </div>\
              </div>\
            </div>\
          </div>';
    $('body').append(html);
    var modal = $('#' + code);
    $('body').append(modal);
    modal.modal('show');
    return modal;
}
function DataManager() {
    this.dataManagers = 0;
}
DataManager.prototype.show = function (item, params) {
    var code = item;
    if (typeof (params.relation) != 'undefined') {
        code += params.relation;
    }
    code = code.replace(':', '_').replace('\\', '_');
    var modal = ui.modal.show('', '<div class = "text-center"><img src = "/static/moduleAsset/Ui/images/ajax-loader.gif" /></div>', code, 'modal-lg');
    $.ajax(
            {
                url: '/admin/ui/dataManager/',
                dataType: 'json',
                data: {item: item, params: params},
                success: function (data) {
                    modal.find('.modal-body').html(data.content)
                }
            }
    );
}
function Form() {
    this.dataManagers = 0;
}
Form.prototype.popUp = function (item, params) {
    var code = item;
    if (typeof (params.relation) != 'undefined') {
        code += params.relation;
    }
    code = code.replace(':', '_').replace('\\', '_');
    var modal = ui.modal.show('', '<div class = "text-center"><img src = "/static/moduleAsset/Ui/images/ajax-loader.gif" /></div>', code, 'modal-lg');
    $.ajax(
            {
                url: '/admin/ui/formPopUp/',
                dataType: 'json',
                data: {item: item, params: params},
                success: function (data) {
                    modal.find('.modal-body').html(data.content)
                }
            }
    );
}
Form.prototype.submitAjax = function (form) {
    var form = $(form);
    var container = form.parent();
    var btn = form.find('button');
    btn.text('Подождите');
    btn[0].disabled = true;
    btn.data('loading-text', "Подождите");

    var formData = new FormData(form[0]);

    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        dataType: 'json',
        data: formData,
        async: false,
        success: function (data) {
            container.html(data.content);
            var btn = container.find('button');
            var text = btn.text();
            btn.text('Изменения сохранены!');
            setTimeout(function () {
                btn.text(text)
            }, 3000);
        },
        cache: false,
        processData: false
    });

}
var ui = new Ui();
ui.modal = new Modal();
ui.form = new Form();
ui.dataManager = new DataManager();