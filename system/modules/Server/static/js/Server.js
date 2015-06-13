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
Server.prototype.request = function (options) {
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
    ajaxOptions.url = inji.options.appRoot+(options.url.replace(/^\//g,''));
    $.ajax(ajaxOptions);
};