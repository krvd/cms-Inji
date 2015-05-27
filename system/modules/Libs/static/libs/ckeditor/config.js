/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
    config.filebrowserBrowseUrl = '/admin/static/filemanager/browser/default/browser.html?Connector=/admin/filemanager';
    config.filebrowserImageBrowseUrl = '/admin/static/filemanager/browser/default/browser.html?Connector=/admin/filemanager';
    config.contentsCss = ['/templates/current/css/editor.css'];
    config.allowedContent = true;
    config.height = '300px';
    config.extraPlugins = 'injiwidgets';
};
