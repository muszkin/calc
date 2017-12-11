(function () {
    'use strict';
    var styles;
    if (localStorage.getItem('styles')) {
        styles = JSON.parse(localStorage.getItem('styles'));
        injectStyles(styles);
    }
    window.shopAppInstance = new ShopApp(function (app) {
        app.init(null, function (params, app) {
            if (localStorage.getItem('styles') === null) {
                injectStyles(params.styles);
            }
            localStorage.setItem('styles', JSON.stringify(params.styles));
            app.show(null, function () {
                app.monitorIframeSize();
                if (window.flashes !== undefined) {
                    showFlashes(flashes);
                }
            });
        }, function (errmsg, app) {
            alert(errmsg);
        });
    }, true);
    function injectStyles (styles) {
        var i;
        var el;
        var sLength;
        sLength = styles.length;
        for (i = 0; i < sLength; ++i) {
            el = document.createElement('link');
            el.rel = 'stylesheet';
            el.type = 'text/css';
            el.href = styles[i];
            document.getElementsByTagName('head')[0].appendChild(el);
        }
    }
}());
