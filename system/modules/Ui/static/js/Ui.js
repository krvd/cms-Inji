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
    this.editors = new Editors();
    this.dataManagers = new DataManagers();
    inji.onLoad(function () {
        inji.Ui.bindMenu($('.nav-list-categorys'));
    });
}
Ui.prototype.bindMenu = function (container) {
    container.find('.nav-left-ml').toggle();
    container.find('label.nav-toggle span').click(function () {
        $(this).parent().parent().children('ul.nav-left-ml').toggle(300);
        var cs = $(this).attr("class");
        if (cs == 'nav-toggle-icon glyphicon glyphicon-chevron-right') {
            $(this).removeClass('glyphicon-chevron-right').addClass('glyphicon-chevron-down');
        }
        if (cs == 'nav-toggle-icon glyphicon glyphicon-chevron-down') {
            $(this).removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-right');
        }
    });
}
/**
 * Editors
 * 
 */
Editors = function () {
    this.checkEditors();
    inji.on('loadScript', function () {
        inji.Ui.editors.checkEditors();
    });
    inji.onLoad(function () {
        inji.Ui.editors.loadIn('.htmleditor');
    })
}
Editors.prototype.checkEditors = function () {
    this.ckeditor = false;
    if (typeof CKEDITOR != 'undefined') {
        this.ckeditor = true;
    }
}
Editors.prototype.loadAll = function () {

}
Editors.prototype.loadIn = function (selector, search) {
    if (this.ckeditor) {
        var instances;
        if (typeof search != 'undefined') {
            instances = $(selector).find(search);
        }
        else {
            instances = $(selector);
        }
        $.each(instances, function () {
            if ($(this).closest('.modal').length == 0 || $(this).closest('.modal').hasClass('in')) {
                $(this).ckeditor();
            }
            if ($(this).closest('.modal').length != 0) {
                var _this = this;
                $(this).closest('.modal').on('shown.bs.modal', function () {
                    $(_this).ckeditor();
                })
                $(this).closest('.modal').on('hide.bs.modal', function () {
                    if ($(_this).next().hasClass('cke')) {
                        var instance = $(_this).next().attr('id').replace('cke_', '');
                        CKEDITOR.instances[instance].updateElement();
                        CKEDITOR.instances[instance].destroy();
                    }
                })
            }
        })
    }
}
Editors.prototype.beforeSubmit = function (form) {
    if (this.ckeditor) {
        $.each($(form).find('.cke'), function () {
            var instance = $(this).attr('id').replace('cke_', '');
            CKEDITOR.instances[instance].updateElement();
            $(CKEDITOR.instances[instance].element).closest('.modal').unbind();
            CKEDITOR.instances[instance].destroy();
        });
    }
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
    if ($('#' + code).length == 0) {
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

    }
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
            modal.find('.modal-body').html(data);
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
    this.limit = 10;
    this.page = 1;
    this.categoryPath = '/';
    var instance = this;
    $(this.element).find('.pagesContainer').on('click', 'a', function () {
        instance.page = $(this).attr('href').match(/page\=(\d+)\&?/)[1];
        instance.limit = $(this).attr('href').match(/limit\=(\d+)\&?/)[1];
        instance.load();
        return false;
    })
    this.load();
}
DataManager.prototype.delRow = function (key) {
    if (confirm('Вы уверены, что хотите удалить элемент?'))
    {
        inji.Server.request({
            url: 'ui/dataManager/delRow',
            data: {params: this.params, modelName: this.modelName, key: key, managerName: this.managerName},
            success: function () {
                inji.Ui.dataManagers.reloadAll();
            }
        });
    }
}
DataManager.prototype.delCategory = function (key) {
    if (confirm('Вы уверены, что хотите удалить элемент?'))
    {
        inji.Server.request({
            url: 'ui/dataManager/delCategory',
            data: {params: this.params, modelName: this.modelName, key: key, managerName: this.managerName},
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
    if (typeof this.params == 'string') {
        var params = JSON.parse(this.params);
    }
    if (Object.prototype.toString.call(this.params) === '[object Array]') {
        var params = {};
    }
    else {
        var params = this.params;
    }
    params.limit = this.limit;
    params.page = this.page;
    params.categoryPath = this.categoryPath;
    filters = {};
    if (this.element.find('.dataManagerFilters [name^="datamanagerFilters"]').length > 0) {
        this.element.find('.dataManagerFilters [name^="datamanagerFilters"]').each(function () {
            var maths = $(this).attr('name').match(/\[([^\]]+)\]/g);
            for (key in maths) {
                maths[key] = maths[key].replace(/([\[\]])/g, '');
            }
            if (!filters[maths[0]]) {
                filters[maths[0]] = {};
            }
            if ($(this).attr('type') == 'checkbox' && !$(this)[0].checked) {
                filters[maths[0]][maths[1]] = 0;
            }
            else {
                filters[maths[0]][maths[1]] = $(this).val();
            }
        });
    }
    dataManager.element.find('tbody').html('<tr><td colspan="' + dataManager.element.find('thead tr th').length + '"><div class = "text-center"><img src = "' + inji.options.appRoot + 'static/moduleAsset/Ui/images/ajax-loader.gif" /></div></td></tr>');
    var instance = this;

    inji.Server.request({
        url: 'ui/dataManager/loadRows',
        data: {params: params, modelName: this.modelName, managerName: this.managerName, filters: filters},
        success: function (data) {
            dataManager.element.find('tbody').html(data.rows);
            dataManager.element.find('.pagesContainer').html(data.pages);
        }
    });
    if (dataManager.element.find('.categoryTree').length > 0) {
        dataManager.element.find('.categoryTree').html('<img class ="img-responsive" src = "' + inji.options.appRoot + 'static/moduleAsset/Ui/images/ajax-loader.gif" />');
        inji.Server.request({
            url: 'ui/dataManager/loadCategorys',
            data: {params: params, modelName: this.modelName, managerName: this.managerName},
            success: function (data) {
                dataManager.element.find('.categoryTree').html(data);
                var active = dataManager.element.find('.categoryTree [data-path="' + instance.categoryPath + '"]');
                if (active.length > 0) {
                    active.parents('.nav-left-ml').css('display', 'none');
                }

                inji.Ui.bindMenu(dataManager.element.find('.categoryTree .nav-list-categorys'));
            }
        });
    }
}
DataManager.prototype.switchCategory = function (categoryBtn) {
    this.categoryPath = $(categoryBtn).data('path');
    this.reload();
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

    if (typeof params == 'undefined') {
        params = {};
    }
    if (typeof (params.relation) != 'undefined') {
        code += params.relation;
    }
    code = code.replace(/:/g, '_').replace(/\\/g, '_');
    var exist = false;
    if ($('#' + code).length != 0) {
        exist = true;
    }
    var modal = inji.Ui.modals.show('', '<div class = "text-center"><img src = "' + inji.options.appRoot + 'static/moduleAsset/Ui/images/ajax-loader.gif" /></div>', code, 'modal-lg');
    if (!exist) {
        inji.Server.request({
            url: 'ui/formPopUp/',
            data: {item: item, params: params},
            success: function (data) {
                modal.find('.modal-body').html(data);
                inji.Ui.editors.loadIn(modal.find('.modal-body'), '.htmleditor');
            }
        });
    }
}
Forms.prototype.submitAjax = function (form) {
    inji.Ui.editors.beforeSubmit(form);
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
            container.html(data);
            inji.Ui.editors.loadIn(container, '.htmleditor');
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
Forms.prototype.delRowFromList = function (btn) {
    $(btn).closest('tr').remove();
}