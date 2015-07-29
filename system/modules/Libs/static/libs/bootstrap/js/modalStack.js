inji.onLoad(function () {
    $('body').on('hidden.bs.modal', '.modal', function (e) {
        if ($('.modal').hasClass('in')) {
            $('body').addClass('modal-open');
        }
    });
});