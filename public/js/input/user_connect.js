"use strict";

$(document).ready(function () {
    let {email, password} = InputHandler.tests;
    let input = new InputHandler(email, password);
    let id = {email: $("#email"), password: $("#password")};

    id.email
        .on(input.EVENTS, function () {
            input.validEmail($(this), email);
        });

    id.password
        .on(input.EVENTS, function () {
            input.validPassword($(this), password);
        });
});


