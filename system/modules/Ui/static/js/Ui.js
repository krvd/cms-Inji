/**
 * Main Ui object
 * 
 * @returns {Ui}
 */
function Ui() {

}
Ui.prototype.init = function () {
    this.modals = new Modals();
    this.forms = new Forms();
    this.dataManagers = new DataManagers();
}
/**
 * Modals objects
 * 
 * @returns {Modals}
 */
Modals = function () {
    this.modals = 0;
}
Modals.prototype.show = function (title, body, code, size) {
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
/**
 * DataManager objects
 * 
 * @returns {DataManagers}
 */
function DataManagers() {
    this.instances = {};
    inji.onLoad(function () {
        $.each($('.dataManager'), function () {
            inji.Ui.dataManagers.instances[$(this).attr('id')] = new DataManager($(this));
        });
    });
}
DataManagers.prototype.get = function (element) {
    if ($(element).hasClass('dataManager')) {
        if (typeof (this.instances[$(element).attr('id')]) != 'undefined') {
            return this.instances[$(element).attr('id')];
        }
        else {
            return this.instances[$(element).attr('id')] = new DataManager($(element));
        }
    }
    else {
        if ($(element).closest('.dataManager').length == 1 && typeof (this.instances[$(element).closest('.dataManager').attr('id')]) != 'undefined') {
            return this.instances[$(element).closest('.dataManager').attr('id')];
        }
        else if ($(element).closest('.dataManager').length == 1) {
            return this.instances[$(element).closest('.dataManager').attr('id')] = new DataManager($(element).closest('.dataManager'));
        }
    }
    return null
}
DataManagers.prototype.popUp = function (item, params) {
    var code = item;
    if (typeof (params.relation) != 'undefined') {
        code += params.relation;
    }
    code = code.replace(':', '_').replace('\\', '_');
    var modal = inji.Ui.modals.show('', '<div class = "text-center"><img src = "' + inji.options.appRoot + 'static/moduleAsset/Ui/images/ajax-loader.gif" /></div>', code, 'modal-lg');
    inji.Server.request({
        url: 'ui/dataManager/',
        dataType: 'json',
        data: {item: item, params: params},
        success: function (data) {
            modal.find('.modal-body').html(data.content);
            $.each(modal.find('.modal-body .dataManager'), function () {
                inji.Ui.dataManagers.instances[$(this).attr('id')] = new DataManager($(this));
            });
        }
    });
}
DataManagers.prototype.reloadAll = function () {
    for (var key in this.instances) {
        this.instances[key].reload();
    }
}
function DataManager(element) {
    this.element = element;
    this.params = element.data('params');
    this.modelName = element.data('modelname');
    this.managerName = element.data('managername');
    this.load();
}
DataManager.prototype.delRow = function (key) {
    if (confirm('Вы уверены, что хотите удалить элемент?'))
    {
        inji.Server.request({
            url: 'ui/dataManager/delRow',
            data: {params: this.params, modelName: this.modelName, key: key},
            success: function () {
                inji.Ui.dataManagers.reloadAll();
            }
        });
    }
}
DataManager.prototype.reload = function () {
    this.load();
}
DataManager.prototype.load = function () {
    var dataManager = this;
    dataManager.element.find('tbody').html('<tr><td colspan="' + dataManager.element.find('thead tr th').length + '"><div class = "text-center"><img src = "' + inji.options.appRoot + 'static/moduleAsset/Ui/images/ajax-loader.gif" /></div></td></tr>');
    inji.Server.request({
        url: 'ui/dataManager/loadRows',
        data: {params: this.params, modelName: this.modelName, managerName: this.managerName},
        success: function (data) {
            dataManager.element.find('tbody').html(data.content);
        }
    });
}
/**
 * Forms object
 * 
 * @returns {Forms}
 */
function Forms() {
    this.dataManagers = 0;
}
Forms.prototype.popUp = function (item, params) {
    var code = item;
    if (typeof (params.relation) != 'undefined') {
        code += params.relation;
    }
    code = code.replace(':', '_').replace('\\', '_');
    var modal = inji.Ui.modals.show('', '<div class = "text-center"><img src = "' + inji.options.appRoot + 'static/moduleAsset/Ui/images/ajax-loader.gif" /></div>', code, 'modal-lg');
    inji.Server.request({
        url: 'ui/formPopUp/',
        data: {item: item, params: params},
        success: function (data) {
            modal.find('.modal-body').html(data.content);
            CKEDITOR.replaceAll(function (textarea, config) {
                console.log(textarea);
                if ($(textarea).hasClass('htmleditor') && $(textarea).css('display') != 'none') {
                    return true;
                }
                else
                {
                    return false;
                }
            });
        }
    });
}
Forms.prototype.submitAjax = function (form) {
    if (typeof CKEDITOR.instances != 'undefined') {
        for (instance in CKEDITOR.instances) {
            CKEDITOR.instances[instance].updateElement();
        }
    }
    var form = $(form);
    var container = form.parent();
    var btn = form.find('button');
    btn.text('Подождите');
    btn[0].disabled = true;
    btn.data('loading-text', "Подождите");

    var formData = new FormData(form[0]);
    inji.Server.request({
        url: form.attr('action'),
        type: 'POST',
        data: formData,
        processData: false,
        success: function (data) {
            container.html(data.content);
            var btn = container.find('form button');
            var text = btn.text();
            btn.text('Изменения сохранены!');
            inji.Ui.dataManagers.reloadAll();
            setTimeout(function () {
                btn.text(text)
            }, 3000);
        }
    });
}
Forms.prototype.addRowToList = function (btn) {
    var container = $(btn).closest('.dynamicList');
    var counter = parseInt(container.find('.sourceRow').data('counter')) + 1;
    container.find('.sourceRow').data('counter', counter);
    var trHtml = container.find('.sourceRow script').html().replace(/^\/\*/g, '').replace(/\*\/$/g, '').replace(/\[counterPlaceholder\]/g, '[' + counter + ']');
    container.find('.listBody').append(trHtml);
}
Forms.prototype.checkAditionals = function (select) {
    var selectedInputAd = $(select).find('option:selected').attr('data-aditionalInput');
    var nextSelect = $(select).next();
    i = 0;
    if ($(select).data('aditionalEnabled') == 1) {
        $(select).data('aditionalEnabled', 0);
        $(select).attr('name', $(select).attr('name').replace(/\[primary\]$/g, ''));
    }
    while (nextSelect.length) {
        if (i != selectedInputAd) {
            nextSelect[0].disabled = true;
            nextSelect.addClass('hidden');
        }
        else {
            if ($(select).data('aditionalEnabled') != 1) {
                $(select).data('aditionalEnabled', 1);
                $(select).attr('name', $(select).attr('name') + '[primary]');
            }
            nextSelect[0].disabled = false;
            nextSelect.removeClass('hidden');
        }
        nextSelect = $(nextSelect).next();
        i++;
    }

}
Forms.prototype.delRowFromList = function(btn){
    $(btn).closest('tr').remove();
}