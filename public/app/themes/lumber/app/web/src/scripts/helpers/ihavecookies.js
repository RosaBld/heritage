import $ from "jquery"; 
require("./ihavecookies.lib");

$(document).ready(function() {
    $('body').ihavecookies(options);

    if ($.fn.ihavecookies.preference('analytics') === true) {
        activateTracking();
    }

    $('#ihavecookiesBtn').on('click', function(e){
        e.preventDefault();
        $('body').ihavecookies(options, 'reinit');
    });
});