function Ui() {
    this.modal = null;
}
Modal = function () {
    this.modals = 0;
}
Modal.prototype.show = function (title, body, code) {
    if (code == null) {
        code = 'modal' + (++this.modals);
    }
    var html = '\
          <div class="modal fade" id = "' + code + '" >\
            <div class="modal-dialog">\
              <div class="modal-content">\
                <div class="modal-header">\
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>\
                  <h4 class="modal-title">' + title + '</h4>\
                </div>\
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
    modal.modal('show');
    return modal;
}
var ui = new Ui();
ui.modal = new Modal();