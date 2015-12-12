/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */
CKEDITOR.plugins.addExternal('inlinesave', '/static/moduleAsset/libs/libs/ckeditor/plugins/inlinesave/');
CKEDITOR.plugins.addExternal('injiwidgets', '/static/moduleAsset/libs/libs/ckeditor/plugins/injiwidgets/');

CKEDITOR.editorConfig = function (config) {
  config.filebrowserBrowseUrl = '/admin/files/managerForEditor?folder=images';
  config.filebrowserImageBrowseUrl = '/admin/files/managerForEditor';
  config.contentsCss = ['/view/editorcss'];
  config.allowedContent = true;
  config.height = '300px';
  config.extraPlugins = 'injiwidgets';
};
CKEDITOR.basePath = inji.options.appRoot + 'static/libs/vendor/ckeditor/ckeditor/ckeditor/';
CKEDITOR.plugins.basePath = inji.options.appRoot + 'static/libs/vendor/ckeditor/ckeditor/ckeditor/plugins/';
