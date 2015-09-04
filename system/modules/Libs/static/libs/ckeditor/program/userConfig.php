/**
* @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
* For licensing, see LICENSE.md or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config ) {
config.filebrowserBrowseUrl = '/admin/files/managerForEditor?folder=images';
config.filebrowserImageBrowseUrl = '/admin/files/managerForEditor';
config.contentsCss = ['/view/editorcss'];
config.allowedContent = true;
config.height = '300px';
config.extraPlugins = 'injiwidgets';
<?php
if (!empty(App::$cur->libs->config['libConfig']['ckeditor']['pasteFromWordRemoveStyle'])) {
    echo 'config.pasteFromWordRemoveStyle = true;';
}
?>
};
