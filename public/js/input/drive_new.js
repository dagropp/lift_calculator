"use strict";

$(document).ready(function () {
    let {origin, destination} = InputHandler.tests;
    let input = new InputHandler(origin, destination);
    let id = {origin: $("#origin"), destination: $("#destination")};
    let history = {origin: "", destination: ""};

    id.origin
        .on(input.EVENTS, function () {
            let val = $(this).val();
            if (!input.testObj[origin].state || val != history.origin) {
                history.origin = val;
                input.validLocation($(this), origin);
            }
        });

    id.destination
        .on(input.EVENTS, function () {
            let val = $(this).val();
            if (!input.testObj[destination].state || val != history.destination) {
                history.destination = val;
                input.validLocation($(this), destination);
            }
        });
});
