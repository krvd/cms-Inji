/**
 * Ecommerce Classes
 */
inji.Ecommerce = {
    Cart: new function () {
        this.addItem = function (itemOfferPriceId, count) {
            inji.Server.request({
                url: 'ecommerce/cart/add',
                data: {
                    itemOfferPriceId: itemOfferPriceId,
                    count: count,
                },
                success: function () {
                    inji.Server.request({
                        url: 'ecommerce/cart/getCart',
                        success: function (data) {
                            $("#cart").html(data);
                        }
                    });
                }
            });
        }
        this.calcSum = function () {
            var sum = 0;
            $('.cartitems .item').each(function () {
                count = parseFloat($(this).find('.cart-couner').val());
                if (isNaN(count))
                    count = 1;
                if ($(this).find('.cart-couner').hasClass('rangerCount')) {
                    count = count / 1000;
                }
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
        this.delItem = function (cart_item_id) {
            data = {};
            data.data = {};
            data.url = '/ecommerce/cart/delcartitem/' + cart_item_id;
            $('.cart_item_id' + cart_item_id).remove();
            data.success = function (data) {
                $('#cart').html(data);
                inji.Ecommerce.Cart.calcSum();
            }
            $.ajax(data);
        }
    }
}
inji.onLoad(function () {

    //plugin bootstrap minus and plus
    //http://jsfiddle.net/laelitenetwork/puJ6G/
    $('body').on('click', '.btn-number', function (e) {
        e.preventDefault();

        fieldName = $(this).attr('data-field');
        type = $(this).attr('data-type');
        var input = $("input[name='" + fieldName + "']");
        var currentVal = parseFloat(input.val());
        if (!isNaN(currentVal)) {
            if (type == 'minus') {

                if (currentVal > input.attr('min')) {
                    input.val(currentVal - 1).change();
                }
                if (parseFloat(input.val()) == input.attr('min')) {
                    $(this).attr('disabled', true);
                }

            } else if (type == 'plus') {

                if (currentVal < input.attr('max')) {
                    input.val(currentVal + 1).change();
                }
                if (parseFloat(input.val()) == input.attr('max')) {
                    $(this).attr('disabled', true);
                }

            }
        } else {
            input.val(0);
        }
    });
    $('body').on('focusin', '.input-number', function () {
        $(this).data('oldValue', $(this).val());
    });
    $('body').on('change', '.input-number', function () {

        minValue = parseFloat($(this).attr('min'));
        maxValue = parseFloat($(this).attr('max'));
        valueCurrent = parseFloat($(this).val());

        name = $(this).attr('name');
        if (valueCurrent >= minValue) {
            $(".btn-number[data-type='minus'][data-field='" + name + "']").removeAttr('disabled')
        } else {
            alert('Нельзя заказать меньше одной единицы товара');
            $(this).val($(this).data('oldValue'));
        }
        if (valueCurrent <= maxValue) {
            $(".btn-number[data-type='plus'][data-field='" + name + "']").removeAttr('disabled')
        } else {
            alert('Извините, но больше нету');
            $(this).val($(this).data('oldValue'));
        }


    });

    $('body').on('keydown', ".input-number", function (e) {
        // Allow: backspace, delete, tab, escape, enter and .
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
                // Allow: Ctrl+A
                        (e.keyCode == 65 && e.ctrlKey === true) ||
                        // Allow: home, end, left, right
                                (e.keyCode >= 35 && e.keyCode <= 39)) {
                    // let it happen, don't do anything
                    return;
                }
                // Ensure that it is a number and stop the keypress
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                    e.preventDefault();
                }
            });

})
