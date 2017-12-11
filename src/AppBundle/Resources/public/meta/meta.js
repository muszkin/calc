var shop_id = parseInt("%d");
var attributeId = parseInt("%d");
var attribute = "%s";
var dtext = "%s";
var url = window.location.origin;
var factor = 0;
var app_url = 'https://calc.dashboarddc.com/productInfo';

var boxHtml = function(label,unit,pack){
    var unitDesc = type2;
    var ret = '<div class="stocks"><span class="label">' + label + '</span><br/><span class="number-wrap"><input name="unit" value="' + unit + '" type="text" class="short inline" style="width:50px !important;"></span><span class="unit">' + unitDesc + ' %s </span><span class="number-wrap"><input name="pack" value="' + pack + '" type="text" class="short inline" style="width:50px !important;">%s</span></div>';

    return ret;
};

var type2 = "%s";

var delay = (function() {
    var timer = {}
        , values = {};
    return function(el) {
        var id = el.form.id + '.' + el.name;
        return {
            enqueue: function(ms, cb) {
                if (values[id] == el.value) return;
                if (!el.value) return;
                var original = values[id] = el.value;
                clearTimeout(timer[id]);
                timer[id] = setTimeout(function() {
                    if (original != el.value) return;
                    cb.apply(el);
                }, ms)
            }
        }
    }
}());

function generatePackageDesc(number){
    var unitDesc = $("div.quantity_wrap > span.unit").text();
    var html = '<em class="unit-info" style="color: #a5a5a5 !important;font-size: 0.9em !important;">('+ number +' '+ unitDesc +')</em>';
    return html;
}

function bindAction(firstInput,secondInput,stockInput,pack,unit,basePrice){
    var price = $('em.main-price');
    $(firstInput).on('keyup',function(){ //always unit calculation (like m2 ,l or something else with decimal places
        delay(this).enqueue(500,function(){
            var u = $(this);
            var p = $(secondInput);
            var number = Math.ceil(parseFloat(u.val())/unit);
            u.val(number * unit);
            p.val(number * pack);
            $(stockInput).val(number);
            if (change_price == true){
                price.html((basePrice * number).toFixed(2) + ' ' + currency  + ' ' + generatePackageDesc(number));
            }
        });
    });
    $(secondInput).on('keyup',function(){ //always pieces input calculation - how much pieces you need to buy to buy one package
        delay(this).enqueue(500,function(){
            var u = $(firstInput);
            var p = $(this);
            var number = Math.ceil(parseFloat(p.val())/pack);
            u.val(number * unit);
            p.val(number * pack);
            $(stockInput).val(number);
            if (change_price == true){
                price.html((basePrice * number).toFixed(2) + ' ' + currency + ' ' + generatePackageDesc(number));
            }
        });
    });
}

$(document).ready(function(){
    var price = $('em.main-price');
    if ($('body').hasClass('shop_product') && $('#box_productdata') != undefined ){
        var product_id = $('body').attr('id').replace('shop_product','');
        $.get(url + '/webapi/front/pl_PL/products/PLN/'+product_id,function(product){
            change_price = (typeof product.unit_calculation !== undefined);
            $.each(product.attributes,function(index,attributes){
                if ( attribute == attributes.name ){
                    $.get(app_url + "/" + shop_id + "/" + product_id,function(data){
                        var productData = $.parseJSON(data);
                        var unitCalculationRatio = parseFloat(productData.stock.calculation_unit_ratio);
                        var package = parseFloat(productData.stock.package);
			            factor = parseFloat(attributes.value.replace(',','.'));
			            var box = boxHtml(dtext,unitCalculationRatio,factor);
                        $('fieldset.addtobasket-container').before(box);
                        currency = "";//price.text().replace(/[0-9\s,\.]/g,'');
			            var basePrice = ((typeof product.price.gross.promo_float === typeof undefined)?product.price.gross.base_float:product.price.gross.promo_float);
			            bindAction('input[name="unit"]','input[name="pack"]','input[name="quantity"]',factor,unitCalculationRatio,basePrice);
                        $('input[name="quantity"]').parent().parent().css({
                            display:"none"
                        });
                    });
                }
            });
        });
    }
});
