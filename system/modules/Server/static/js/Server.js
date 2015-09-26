/**
 * Item name
 *
 * Info
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

function Server() {

}
Server.prototype.request = function (options, btn) {
  var ajaxOptions = {
    url: '',
    type: 'GET',
    dataType: 'json',
    data: {},
    async: true,
    contentType: false,
    cache: false,
  };
  for (var key in options) {
    ajaxOptions[key] = options[key];
  }
  if (options.url && options.url.indexOf(inji.options.appRoot) !== 0) {
    ajaxOptions.url = inji.options.appRoot + (options.url.replace(/^\//g, ''));
  }
  if (typeof btn != 'undefined') {
    $(btn).data('loading-text', 'подождите');
    var btn = $(btn).button().button('loading');
  }
  var callback = null;
  if (typeof options.success != 'undefined') {
    callback = options.success;
  }
  ajaxOptions.success = function (data, textStatus, jqXHR) {
    if (typeof btn != 'undefined') {
      btn.button('reset');
    }
    if (ajaxOptions.dataType != 'json') {
      callback(data, textStatus, jqXHR);
    }
    else {
      if (data.success) {
        if (data.successMsg) {
          noty({text: data.successMsg, type: 'success', timeout: 3500, layout: 'center'});
        }
        if (typeof data.scripts == 'object') {
          inji.loaded = false;
          if (callback !== null) {
            inji.onLoad(function () {
              callback(data.content, textStatus, jqXHR)
            });
          }
          inji.loadScripts(data.scripts, 0);
        }
        else {
          if (callback !== null) {
            callback(data.content, textStatus, jqXHR);
          }
        }
      }
      else {
        noty({text: data.error, type: 'warning', timeout: 3500, layout: 'center'});
      }
    }
  }
  var errorCallback = null;
  if (typeof options.error != 'undefined') {
    errorCallback = options.error;
  }
  ajaxOptions.error = function (jqXHR, textStatus, errorThrown) {
    if (typeof btn != 'undefined') {
      btn.button('reset');
    }
    if (errorCallback != null) {
      errorCallback(jqXHR, textStatus, errorThrown);
    } else {
      noty({text: 'Во время запроса произошла ошибка: ' + textStatus, type: 'warning', timeout: 3500, layout: 'center'});
    }
  }
  $.ajax(ajaxOptions);
};