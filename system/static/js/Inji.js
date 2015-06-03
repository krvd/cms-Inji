/**
 * Inji js core
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

function Inji() {
    this.options = {};
    this.onLoadCallbacks = [];
    this.loaded = false;
}
Inji.prototype.onLoad = function (callback) {
    if (this.loaded) {
        callback();
    }
    else {
        this.onLoadCallbacks.push(callback);
    }
}
Inji.prototype.startCallbacks = function () {
    console.log('start onLoadeds');
    for (var key in this.onLoadCallbacks) {
        this.onLoadCallbacks[key]();
    }
    document.getElementById('loading-indicator').style.display = 'none';
    inji.loaded = true;
    console.log('inji start complete');
}
Inji.prototype.start = function (options) {
    console.log('Inji start');
    this.options = options;
    this.loadScripts(options.scripts, 0);
}
Inji.prototype.loadScripts = function (scripts, key) {
    this.addScript(scripts[key], function () {
        if (typeof (scripts[key].name) != 'undefined') {
            console.log('js ' + scripts[key].name + '(' + scripts[key].file + ') loaded');
            inji[scripts[key].name] = new window[scripts[key].name]();
            if (typeof (inji[scripts[key].name].init) == 'function') {
                inji[scripts[key].name].init();
            }
        }
        else {
            console.log('js ' + scripts[key] + ' loaded');
        }
        if (typeof (scripts[key + 1]) != 'undefined') {
            inji.loadScripts(scripts, key + 1);
        }
        else {
            console.log('All scripts loaded');
            inji.startCallbacks();
        }
    });
}
Inji.prototype.addScript = function (script, callback) {
    var element = document.createElement('script');
    element.type = 'text/javascript';
    if (typeof (callback) == 'function') {
        element.onload = callback;
    }
    if (typeof (script.file) != 'undefined') {
        element.src = script.file;
    }
    else {
        element.src = script;
    }
    document.head.appendChild(element);
}
var inji = new Inji();

