
$.getMultiScripts = function(arr) {
    var _arr = $.map(arr, function(src) {
        return $.getScript(src);
    });
    _arr.push($.Deferred(function( deferred ){
        $( deferred.resolve );
    }));
    return $.when.apply($, _arr);
}

$.expr[':'].contains = function(a, i, m) {
    return $(a).text().toUpperCase()
        .indexOf(m[3].toUpperCase()) >= 0;
};

$.fn.serializeFormJSON = function () {

    var o = {};
    var a = this.serializeArray();
    $.each(a, function () {
        if (o[this.name]) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};
