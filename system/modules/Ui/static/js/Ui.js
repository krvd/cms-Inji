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
  this.ckeditor = false;
  this.checkEditors();
  inji.on('loadScript', function () {
    inji.Ui.editors.checkEditors();
  });
  inji.onLoad(function () {
    inji.Ui.editors.loadIn('.htmleditor');
  })
}
Editors.prototype.checkEditors = function () {
  if (!this.ckeditor && typeof CKEDITOR != 'undefined') {
    CKEDITOR.basePath = window.CKEDITOR_BASEPATH = inji.options.appRoot + 'static/moduleAsset/libs/libs/ckeditor/';
    CKEDITOR.plugins.basePath = inji.options.appRoot + 'static/moduleAsset/libs/libs/ckeditor/plugins/';
    this.ckeditor = true;
  }
}
Editors.prototype.loadAll = function () {

}
Editors.prototype.loadIn = function (selector, search) {
  if (this.ckeditor) {
    setTimeout(function () {
      var instances;
      if (typeof search != 'undefined') {
        instances = $(selector).find(search);
      } else {
        instances = $(selector);
      }
      $.each(instances, function () {
        if ($(this).closest('.modal').length == 0 || $(this).closest('.modal').hasClass('in')) {
          var editor = $(this).ckeditor({customConfig: inji.options.appRoot + 'static/moduleAsset/libs/libs/ckeditor/program/userConfig.php'});
        }
        if ($(this).closest('.modal').length != 0) {
          var _this = this;
          $(this).closest('.modal').on('shown.bs.modal', function () {
            $(_this).ckeditor({customConfig: inji.options.appRoot + 'static/moduleAsset/libs/libs/ckeditor/program/userConfig.php'});
          })
          $(this).closest('.modal').on('hide.bs.modal', function () {
            if ($(_this).next().hasClass('cke')) {
              var instance = $(_this).next().attr('id').replace('cke_', '');
              if (CKEDITOR.instances[instance]) {
                CKEDITOR.instances[instance].updateElement();
                CKEDITOR.instances[instance].destroy();
              }

            }
          })
        }
      })
    }, 500);
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
    } else {
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
    $('.batch_action').click(function () {
      var ids = '';
      var rows = {};
      var instance = inji.Ui.dataManagers.get($(this).closest('#main_content').find('.dataManager'));
      $(instance.element).find('tbody tr').each(function () {
        if ($($(this).find('td').get(0)).find('[type="checkbox"]')[0].checked) {
          ids += ',' + $($(this).find('td').get(0)).find('[type="checkbox"]').val();
          rows[$($(this).find('td').get(0)).find('[type="checkbox"]').val()] = $(this);
        }
      });
      var actionName = $(this).data('action');
      var action = instance.options.groupActions[actionName];
      if (ids != '') {
        if (action.customJsChecker) {
          if (!window[action.customJsChecker](instance, rows)) {
            return;
          }
        }
        if (action.aditionalInfo) {
          var id = inji.randomString();
          html = '<form id ="' + id + '"><h3>Для этой груповой операции требуется дополнительная информация</h3>';
          for (key in action.aditionalInfo) {
            var input = action.aditionalInfo[key];
            html += '<div class = "form-group"><label>' + input.label + '</label><input type="' + input.type + '" name ="' + key + '" class = "form-control" value = "" /></div>';
          }
          html += '<div class = "form-group"><button class="btn btn-primary" >' + action.name + '</button></div></form>';
          inji.Ui.modals.show('Дополнительная информация', html, 'modal' + id);
          $('#' + id).submit(function () {
            $(this).closest('.modal').modal('hide');
            var adInfo = {};
            if ($(this).find('input').length > 0) {
              $.each($(this).find('input'), function () {
                adInfo[$(this).attr('name')] = $(this).val();
              });
            }
            inji.Server.request({
              url: 'ui/dataManager/groupAction',
              data: {params: instance.params, modelName: instance.modelName, ids: ids, managerName: instance.managerName, action: actionName, adInfo: adInfo},
              success: function () {
                inji.Ui.dataManagers.reloadAll();
              }
            });
            return false;
          });
        } else {
          inji.Server.request({
            url: 'ui/dataManager/groupAction',
            data: {params: instance.params, modelName: instance.modelName, ids: ids, managerName: instance.managerName, action: actionName},
            success: function () {
              inji.Ui.dataManagers.reloadAll();
            }
          });
        }
      }
      $(this).closest('.dropdown_menu_list_wrapper').slideToggle();
    });
  });
}
DataManagers.prototype.get = function (element) {
  if ($(element).hasClass('dataManager')) {
    if (typeof (this.instances[$(element).attr('id')]) != 'undefined') {
      return this.instances[$(element).attr('id')];
    } else {
      return this.instances[$(element).attr('id')] = new DataManager($(element));
    }
  } else {
    if ($(element).closest('.dataManager').length == 1 && typeof (this.instances[$(element).closest('.dataManager').attr('id')]) != 'undefined') {
      return this.instances[$(element).closest('.dataManager').attr('id')];
    } else if ($(element).closest('.dataManager').length == 1) {
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
  code = code.replace(/\:/g, '_').replace(/\\/g, '_');
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
  this.options = element.data('options');
  this.limit = 30;
  this.page = 1;
  this.sortered = {};
  this.categoryPath = '/';
  this.mode = '';
  this.all = 0;
  this.ajaxUrl = 'ui/dataManager/loadRows';
  if (this.options.ajaxUrl) {
    this.ajaxUrl = this.options.ajaxUrl;
  }
  var instance = this;
  $(this.element).find('thead [type="checkbox"],tfoot [type="checkbox"]').click(function () {
    var index = $(this).closest('th').index();
    if (!this.checked) {
      $(instance.element).find('tbody tr').each(function () {
        $($(this).find('td').get(index)).find('[type="checkbox"]')[0].checked = false;
      });
    } else {
      $(instance.element).find('tbody tr').each(function () {
        $($(this).find('td').get(index)).find('[type="checkbox"]')[0].checked = true;
      });
    }
  });
  if (this.options.sortMode) {
    $(this.element).find('.modesContainer').on('click', 'a', function () {
      if (instance.mode != $(this).data('mode')) {
        instance.mode = $(this).data('mode');
        instance.all = 1;
        instance.page = 1;
        instance.load();
      } else {
        instance.mode = '';
        instance.all = 0;
        instance.page = 1;
        instance.load();
      }
    });
  }
  $(this.element).find('.pagesContainer').on('click', 'a', function () {
    instance.page = $(this).attr('href').match(/page\=(\d+)\&?/)[1];
    instance.limit = $(this).attr('href').match(/limit\=(\d+)\&?/)[1];
    instance.load();
    return false;
  });
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
DataManager.prototype.load = function (options) {
  var dataManager = this;
  if (typeof this.params == 'string') {
    var params = JSON.parse(this.params);
  }
  if (Object.prototype.toString.call(this.params) === '[object Array]') {
    var params = {};
  } else {
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
      } else {
        filters[maths[0]][maths[1]] = $(this).val();
      }
    });
  }
  if (this.options.sortable) {
    sortableIndexes = [];
    var i = 0;
    for (key2 in  this.options.cols) {
      var colname;
      if (typeof this.options.cols[key2] == 'object') {
        colname = key2;
      } else {
        colname = this.options.cols[key2];
      }
      for (key in this.options.sortable) {
        if (colname == this.options.sortable[key]) {
          sortableIndexes.push(parseInt(i));
        }
      }
      i++;
    }
    for (key in sortableIndexes) {
      var shift = 1;
      if (this.options.groupActions) {
        shift++;
      }
      var headTh = $(dataManager.element.find('thead th').get(sortableIndexes[key] + shift));
      var footTh = $(dataManager.element.find('tfoot th').get(sortableIndexes[key] + shift));
      if (!headTh.hasClass('sortable')) {
        headTh.html('<a href = "#">' + headTh.html() + '</a>');
        headTh.addClass('sortable');
        if (this.options.preSort && this.options.preSort[this.options.sortable[key]]) {
          if (this.options.preSort[this.options.sortable[key]] == 'asc') {
            headTh.addClass('sorted-asc');
            this.sortered[sortableIndexes[key]] = 'asc';
          } else if (this.options.preSort[this.options.sortable[key]] == 'desc') {
            headTh.addClass('sorted-desc');
            this.sortered[sortableIndexes[key]] = 'desc';
          }
        }
        //sorted-desc
        headTh.click(function () {
          $(this).addClass('clickedsort');
          $('.sortable').not('.clickedsort').removeClass('sorted-asc').removeClass('sorted-desc');
          $(this).removeClass('clickedsort');
          dataManager.sortered = {};
          if (!$(this).hasClass('sorted-desc') && !$(this).hasClass('sorted-asc')) {
            $(this).addClass('sorted-desc');
            dataManager.sortered[$(this).index() - shift] = 'desc';
            dataManager.reload();
          } else if ($(this).hasClass('sorted-desc')) {
            $(this).removeClass('sorted-desc');
            $(this).addClass('sorted-asc');
            dataManager.sortered[$(this).index() - shift] = 'asc';
            dataManager.reload();
          } else if ($(this).hasClass('sorted-asc')) {
            $(this).removeClass('sorted-asc');
            delete dataManager.sortered[$(this).index() - shift];
            dataManager.reload();
          }
          return false;
        })
      }
      if (!footTh.hasClass('sortable')) {
        footTh.html('<a href = "#">' + footTh.html() + '</a>');
        footTh.addClass('sortable');
        footTh.click(function () {
          $(this).addClass('clickedsort');
          $('.sortable').not('.clickedsort').removeClass('sorted-asc').removeClass('sorted-desc');
          $(this).removeClass('clickedsort');
          dataManager.sortered = {};
          if (!$(this).hasClass('sorted-desc') && !$(this).hasClass('sorted-asc')) {
            $(this).addClass('sorted-desc');
            dataManager.sortered[$(this).index() - shift] = 'desc';
            dataManager.reload();
          } else if ($(this).hasClass('sorted-desc')) {
            $(this).removeClass('sorted-desc');
            $(this).addClass('sorted-asc');
            dataManager.sortered[$(this).index() - shift] = 'asc';
            dataManager.reload();
          } else if ($(this).hasClass('sorted-asc')) {
            $(this).removeClass('sorted-asc');
            delete dataManager.sortered[$(this).index() - shift];
            dataManager.reload();
          }
          return false;
        })
      }
    }
  }
  var data = {params: params, modelName: this.modelName, managerName: this.managerName, filters: filters, sortered: this.sortered, mode: this.mode, all: this.all};
  if (options && options.download) {
    data.download = true;
    var url = this.ajaxUrl;
    if (url.indexOf(inji.options.appRoot) !== 0) {
      url = inji.options.appRoot + url;
    }
    window.location = url + '?' + $.param(data);
    return;
  }
  dataManager.element.find('tbody').html('<tr><td colspan="' + dataManager.element.find('thead tr th').length + '"><div class = "text-center"><img src = "' + inji.options.appRoot + 'static/moduleAsset/Ui/images/ajax-loader.gif" /></div></td></tr>');
  var instance = this;

  inji.Server.request({
    url: this.ajaxUrl,
    data: data,
    success: function (data) {
      console.log(instance.element);
      dataManager.element.find('tbody').html(data.rows);
      dataManager.element.find('.pagesContainer').html(data.pages);
      if (dataManager.options.sortMode) {
        dataManager.element.find('.modesContainer').html('<a class ="btn btn-xs btn-default" data-mode="sort">' + (dataManager.mode != 'sort' ? 'Включить' : 'Выключить') + ' режим сортировки</a>');
      }
      $(instance.element).find('tbody').sortable().sortable("disable");
      if (dataManager.mode == 'sort') {
        $(instance.element).find('tbody').sortable({
          stop: function (event, ui) {
            ids = $(instance.element).find('tbody tr');
            i = 0;
            while (ids[i]) {
              var key = $(ids[i++]).find('td').get(0).innerHTML;
              inji.Server.request({
                url: 'ui/dataManager/updateRow',
                data: {params: instance.params, modelName: instance.modelName, key: key, col: 'weight', col_value: i, managerName: instance.managerName, silence: true},
              });
            }
          }
        }).sortable("enable");
      }
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
          var child = $($($(active).parent().parent().get(0)).children().get(1)).children().get(0);
          if (child) {
            //$(child).parents().map(function(){$(this).find('.glyphicon-chevron-right:first').removeClass('glyphicon-chevron-right').addClass('glyphicon-chevron-down');});
            $(child).parents('.nav-left-ml').css('display', 'none');
          } else {
            active.parents('.nav-left-ml').css('display', 'none');
          }
          active.css('fontWeight', 'bold')
        }

        inji.Ui.bindMenu(dataManager.element.find('.categoryTree .nav-list-categorys'));
        $(instance.element).find('.categoryTree').sortable().sortable("disable");
        if (dataManager.mode == 'sort') {
          $(instance.element).find('.categoryTree ul a[data-path]').map(function () {
            this.onclick = null
          });
          $(instance.element).find('.categoryTree ul').sortable({
            stop: function (event, ui) {
              ids = $(instance.element).find('li');
              i = 0;
              while (ids[i]) {
                var key = $(ids[i]).data('id');
                var model = $(ids[i]).data('model');
                if (key && model) {
                  inji.Server.request({
                    url: 'ui/dataManager/updateRow',
                    data: {params: instance.params, modelName: model, key: key, col: 'weight', col_value: i, managerName: instance.managerName, silence: true},
                  });
                }
                i++;
              }
            }
          }).sortable("enable");
        }
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
  //if (!exist) {
  inji.Server.request({
    url: 'ui/formPopUp/',
    data: {item: item, params: params},
    success: function (data) {
      modal.find('.modal-body').html(data);
      inji.Ui.editors.loadIn(modal.find('.modal-body'), '.htmleditor');
    }
  });
  //}
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
  }
  while (nextSelect.length) {
    if (i != selectedInputAd) {
      nextSelect[0].disabled = true;
      nextSelect.addClass('hidden');
    } else {
      if ($(select).data('aditionalEnabled') != 1) {
        $(select).data('aditionalEnabled', 1);
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