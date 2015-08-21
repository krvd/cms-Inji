/* 
 * The MIT License
 *
 * Copyright 2015 Alexey Krupskiy <admin@inji.ru>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
var procTimeot = null;
function calcsum() {
    calcsumProc();
}
function calcsumProc() {
    console.log('calc');
    var sum = 0;
    $('.cartitems .item').each(function () {
        count = parseFloat($(this).find('.cart-couner').val());
        if (isNaN(count))
            count = 1;
        if ($(this).find('.cart-couner').hasClass('rangerCount')) {
            count = count / 1000;
        }
        console.log(count);
        sum += parseFloat($(this).data('priceam')) * count;
        data = {};
        data.data = {};
        data.data.cart_item_id = $(this).data('cart_item_id');
        data.data.count = count;
        data.data.item_offer_price_id = $(this).data('item_offer_price_id');
        data.url = '/ecommerce/cart/updatecartitem';
        data.success = function (data) {
            $('#cart').html(data);
        };
        $.ajax(data);
        var price = $(this).data('priceam');
        console.log(price);
        $(this).find('.total').html(price * count + ' руб.');
    });
    asum = sum;
    if (typeof (deliverys) != 'undefined') {
        delivery = deliverys[$('[name="delivery"]').val()];
        if (sum >= parseFloat(delivery.delivery_max_cart_price)) {
            $($('.deliverysum td').get(1)).html('0 руб.');
            asum = sum;
        }
        else {
            $($('.deliverysum td').get(1)).html(parseFloat(delivery.delivery_price) + ' руб.');
            asum = sum + parseFloat(delivery.delivery_price);
        }
        $($('.deliverysum td').get(0)).html(delivery.delivery_name + ':');
    }
    else {
        $($('.deliverysum td').get(0)).html('Без доставки');
        $($('.deliverysum td').get(1)).html('0 руб.');
    }
    $($('.cartsums td').get(1)).html(sum.toFixed(2) + ' руб.');
    var packsCkeckbox = $('[name="packs"]');
    var packSums = 0;
    $('.packsCount').html(Math.ceil(sum / 1000));
    if (packsCkeckbox.length > 0) {
        if (packsCkeckbox[0].checked) {
            packSums = (Math.ceil(sum / 1000) * parseFloat(packsCkeckbox.val()));
            $($('.packssum td').get(1)).html(packSums.toFixed(2) + ' руб.');
        }
        else {
            packSums = 0;
            $($('.packssum td').get(1)).html('0 руб.');
        }
    }
    $($('.allsums td').get(1)).html((asum + packSums).toFixed(2) + ' руб.');
}

function addToCart(btn, ci_id, ciprice_id, count, countInputSelector, tokg) {
    var data = {};
    var btn = $(btn).button('loading');
    btn.data('loading-text', "Подождите");
    data.data = {};
    data.data.ci_id = ci_id;
    if (count != null && typeof (countInputSelector) != 'undefinded') {
        data.data.count = count;
    }
    else if (countInputSelector != null && typeof (countInputSelector) != 'undefinded') {
        data.data.count = $(btn).closest('.catalog-item').find(countInputSelector).val();
        if (tokg) {
            data.data.count = data.data.count / 1000;
        }
    }
    else {
        data.data.count = 1;
    }
    data.data.price = ciprice_id;
    data.url = '/ecommerce/cart/add';
    data.success = function (data) {
        console.log(data.indexOf('1'));
        if (data.indexOf('1') === 0) {
            noty({text: 'Данного количества нет в наличии. Для покупки доступно: ' + data.replace('1|', ''), type: 'warning', timeout: 3500, layout: 'center'});
        } else if (data != '0') {
            noty({text: 'Товар успешно добавлен в корзину', type: 'success', timeout: 1500, layout: 'center'});
            $('.cart-dropdown').html(data);
        } else if (data == '0') {
            noty({text: 'По какой то причине товар не удалось добавить в корзину', type: 'error', timeout: 1500, layout: 'center'});
        }
        btn.button('reset');
    };
    $.ajax(data);
    return false;
}
function cartdel(cart_item_id) {
    data = {};
    data.data = {};
    data.url = '/ecommerce/cart/delcartitem/' + cart_item_id;
    $('.cart_item_id' + cart_item_id).remove();
    data.success = function (data) {
        $('#cart').html(data);
        calcsum();
    }
    $.ajax(data);
}
