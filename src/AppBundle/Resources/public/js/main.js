var Settings;

Settings = {
    selectors:{
        newAttributes:'.new_attr',
        attributes:'#appbundle_settings_attribute_id',
        yes:'#attribute_yes',
        no:'#attribute_no',
        products:'#products',
        url:'#url',
        result:'#result',
        progressbar:'#progressbar-bar',
        progressbartext:'#progressbar-progress',
        infoRow:'#info-row',
        finish:'#finish',
        errors:'#errors'
    },
    percent: 0,
    processed: 0,
    total: 0,
    request: 0,
    newAttributesAction:function(){
        $('.checkbox > label').contents().filter(function(){
            return (this.nodeType == 3);
        }).remove();
        if ($(Settings.selectors.attributes).val() != 'new'){
            $(Settings.selectors.newAttributes).css({
                display:'none'
            });
        }else{
            $(Settings.selectors.newAttributes).css({
                display:'inherit'
            });
        }
        $(Settings.selectors.attributes).on('change',function(){
            var select = $(this);
            if (select.val() != 'new'){
                $(Settings.selectors.newAttributes).css({
                    display:'none'
                });
            }else{
                $(Settings.selectors.newAttributes).css({
                    display:'inherit'
                });
            }
        });

    },
    postRequest:function(url,productArray){
        if (productArray.length > 0) {
            var id = productArray.shift();
            $.post(url, {product_id:id}, function (e) {
                if (e.errors != undefined) {
                    $(Settings.selectors.errors).append('<p>' + e.errors + '</p>');
                }
                Settings.processed++;
                Settings.progress.progress();
                if (Settings.processed == Settings.total) {
                    Settings.progress.finish();
                }else{
                    Settings.postRequest(url,productArray);
                }
            });
        }
    },
    productlistActions:function(){
        $(Settings.selectors.result).css({
            "display":"none"
        });
        $(Settings.selectors.finish).css('display','none');
        $(Settings.selectors.no).on('click',function(){
            window.shopAppInstance.closeIframeModal();
        });
        $(Settings.selectors.yes).on('click',function(){
            var products = $.parseJSON($(Settings.selectors.products).val());
            Settings.total = products.length;
            Settings.progress.init();
            var url = $(Settings.selectors.url).val();
            Settings.postRequest(url,products);
        });
    },
    progress:{
        init:function(){
            $(Settings.selectors.result).css("display","inherit");
            $(Settings.selectors.progressbartext).text(progressBarTitle);
            $(Settings.selectors.infoRow).css("display","none");
        },
        progress:function(){
            Settings.percent = (Math.round(Settings.processed * 100 / Settings.total)*100)/100;
            $(Settings.selectors.progressbar).attr('aria-valuenow',Settings.percent);
            $(Settings.selectors.progressbar).css('width',Settings.percent + '%');
            $(Settings.selectors.progressbar).text(Settings.percent + progressBarProgress);
        },
        finish:function(){
            $(Settings.selectors.result).css("display","none");
            $(Settings.selectors.finish).css('display','inherit');
            if ($(Settings.selectors.errors).text().length > 0){
                $(Settings.selectors.finish).append($(Settings.selectors.errors).html());
            }
        }
    },
    init:function(){
        Settings.newAttributesAction();
        Settings.productlistActions();
    }
};



$(document).ready(function(){
    $('#appbundle_settings_active').bootstrapSwitch();
    Settings.init();
});