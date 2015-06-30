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
    this.listeners = {};
    this.loadedScripts = {};
}
Inji.prototype.onLoad = function (callback) {
    if (typeof callback == 'function') {
        if (this.loaded) {
            callback();
        }
        else {
            this.onLoadCallbacks.push(callback);
        }
    }
}
Inji.prototype.startCallbacks = function () {
    console.log('inji start onload');
    while (callback = this.onLoadCallbacks.shift()) {
        if (typeof callback == 'function') {
            callback();
        }
    }
    if (this.onLoadCallbacks.length != 0) {
        this.startCallbacks();
    }
    var indicator = document.getElementById('loading-indicator');
    if (indicator) {
        indicator.style.display = 'none';
    }
    inji.loaded = true;
    console.log('inji start complete');
}
Inji.prototype.start = function (options) {
    console.log('Inji start');
    this.options = options;
    if (options.onLoadModules) {
        this.onLoad(function () {
            for (key in options.onLoadModules) {
                if (typeof inji[key] == 'undefined') {
                    console.log(key);
                    inji[key] = new window[key]();
                    if (typeof (inji[key].init) == 'function') {
                        console.log(key + ' init');
                        inji[key].init();
                    }
                }
            }
        })
    }
    this.loadScripts(options.scripts, 0);
}
Inji.prototype.loadScripts = function (scripts, key) {
    this.addScript(scripts[key], function () {
        if (typeof (scripts[key].name) != 'undefined') {
            inji.loadedScripts[scripts[key].file] = true;
            if (typeof inji[scripts[key].name] == 'undefined') {
                console.log('js ' + scripts[key].name + '(' + scripts[key].file + ') loaded');
                inji[scripts[key].name] = new window[scripts[key].name]();
                if (typeof (inji[scripts[key].name].init) == 'function') {
                    inji[scripts[key].name].init();
                }
            }
        }
        else {
            inji.loadedScripts[scripts[key]] = true;
            console.log('js ' + scripts[key] + ' loaded');
            inji.event('loadScript', scripts[key]);
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
    var src = '';
    if (typeof (script.file) != 'undefined') {
        src = script.file;
    }
    else {
        src = script;
    }
    if (inji.loadedScripts[src]) {
        if (typeof (callback) == 'function') {
            callback();
        }
        return true;
    }
    element.src = src;
    element.type = 'text/javascript';
    if (typeof (callback) == 'function') {
        element.onload = callback;
    }
    document.head.appendChild(element);


}
Inji.prototype.on = function (eventType, callback) {
    if (typeof this.listeners[eventType] == 'undefined') {
        this.listeners[eventType] = [];
    }
    this.listeners[eventType].push(callback);
}
Inji.prototype.event = function (eventType, object) {
    if (typeof this.listeners[eventType] != 'undefined') {
        for (key in this.listeners[eventType]) {
            this.listeners[eventType][key](eventType, object);
        }
    }
}
var inji = new Inji();

