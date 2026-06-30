(function () {
    var scripts = [
        'https://editorial.wiredhowse.com/util/lib/js/app-loader/whapp.js',
        'https://editorial.wiredhowse.com/util/lib/js/app-loader/app.js'
    ];

    function loadNext(i) {
        if (i >= scripts.length) return;
        var s = document.createElement('script');
        s.src = scripts[i];
        s.defer = true;
        s.onload = function () {
            loadNext(i + 1);
        };
        document.head.appendChild(s);
    }

    loadNext(0);
})();
