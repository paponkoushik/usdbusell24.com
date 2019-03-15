jQuery(function ($) {

    'use strict';

    $("#exchangeBtn").on("click", function (e) {
        $.getJSON( "/api/public-providers", function( data ) {
            console.log(data);

            data.forEach(function (element) {
                console.log(element.name);
            })
        });
    })
});