$(function () {
    $(".blockPreset .block,.blockMap .block").draggable({
        appendTo: "body",
        revert: true,
    });
    $(".blockMap .blockCol, .blockPreset").droppable({
        activeClass: "ui-state-default",
        hoverClass: "ui-state-hover",
        accept: ":not(.ui-sortable-helper)",
        drop: function (event, ui) {
            $(this).find(".placeholder").remove();
            $("<div class ='block' data-code='" + ui.draggable.data('code') + "'></div>").text(ui.draggable.text()).appendTo(this);
            if (ui.draggable.closest('.rows').length > 0) {
                ui.draggable.remove();
            }
        }
    }).sortable({
        items: "div:not(.placeholder)",
        sort: function () {
            $(this).removeClass("ui-state-default");
        }
    });
});

function BlockDrop() {
    this.rows = 0;
}


BlockDrop.prototype.addRow = function (selector) {
    var html = '<div class = "rowsSelector">';
    html += '<div class = "form-group">';
    html += '<label>Число столбцов</label><div class = "cleaner"></div>';
    html += '<div class="btn-group" data-toggle="buttons">';
    for (var i = 1; i <= 12; i++) {
        html += '<label class="btn btn-primary colsRadio" onclick="blockDrop.changeCols(this,' + i + ')">\
                    <input type="radio" name="options" autocomplete="off"> ' + i + '\
                </label>';
    }
    html += '</div>';
    html += '</div>';
    html += '<div class = "row colsSelect">';
    html += '<div class = "blockCol col-xs-12">12</div>';
    html += '</div>';
    html += '<div class = "form-group"><button class = "btn btn-success" onclick = "blockDrop.acceptAddRow(this);" data-dismiss="modal">Добавить строку</button></div>';
    html += '</div>';
    var modal = ui.modal.show('Выбор разметки', html);
}
BlockDrop.prototype.changeCols = function (btn, count) {
    var rowselector = $(btn).closest('.rowsSelector');
    rowselector.find('.colsSelect').html('');
    var parts = 0;
    var part = parseInt(12 / count);
    for (var i = 1; i <= count; i++) {
        parts += part;
        if (parts > 12) {
            part = parts - 12;
        }
        else if (i == count && parts < 12) {
            part = part + 12 - parts;
        }
        rowselector.find('.colsSelect').append('<div class = "blockCol col-xs-' + part + '">' + part + '</div>');
    }
}

BlockDrop.prototype.acceptAddRow = function (btn) {
    var rowselector = $(btn).closest('.rowsSelector');
    var row = rowselector.find('.colsSelect').clone();
    $.each(row.find('.blockCol'), function () {
        $(this).html('');
    })
    $('.blockMap .rows').append(row);
    $(".blockMap .blockCol").droppable({
        activeClass: "ui-state-default",
        hoverClass: "ui-state-hover",
        accept: ":not(.ui-sortable-helper)",
        drop: function (event, ui) {
            $(this).find(".placeholder").remove();
            $("<div class ='block'  data-code='" + ui.draggable.data('code') + "'></div>").text(ui.draggable.text()).appendTo(this).draggable({
                appendTo: "body",
                revert: true,
            });
            ui.draggable.remove();
        }
    }).sortable({
        items: "div:not(.placeholder)",
        sort: function () {
            $(this).removeClass("ui-state-default");
        }
    });
}

BlockDrop.prototype.submitMap = function (btn) {
    var form = $(btn).closest('form');
    var map = form.find('.rows');
    $.each(map.find('.block'), function () {
        $(this).html($(this).data('code'));
        $(this).removeAttr('data-code');
    })
    form.find('[name="map"]').val(map.html());
    //console.log(map.html())
    form.submit();
}
BlockDrop.prototype.initActual = function (selector) {
    $.each($(selector).find('.block'), function () {
        var code = $(this).text();
        $(this).text($('[data-code="' + code + '"]').text());
        $(this).data('code', code);
    });
}
var blockDrop = new BlockDrop();
