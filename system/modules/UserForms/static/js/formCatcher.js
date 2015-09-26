

function popUpForm(formId, title) {
  if ($('#popUpForm' + formId).length == 0) {
    var html = '\
                <div class="modal fade " id = "popUpForm' + formId + '">\
                    <div class="modal-dialog modal-sm">\
                      <div class="modal-content">\
                        <div class="modal-header">\
                          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>\
                          <h4 class="modal-title">' + title + '</h4>\
                        </div>\
                        <div class="modal-body">\
                          <p class = "text-center"><img src = "http://www.infinite-scroll.com/loading.gif"/></p>\
                        </div>\
                        <div class="modal-footer">\
                          <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>\
                        </div>\
                      </div>\
                    </div>\
                  </div>';
    $('body').append(html);
  }
  var modal = $('#popUpForm' + formId);
  modal.modal('show');
  $.get('/UserForms/getFormHtml/' + formId, function (html) {
    modal.find('.modal-body').html(html);
  });
  return false;
}