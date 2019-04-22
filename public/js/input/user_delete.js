"use strict";

$(document).ready(function () {
    let {password, passwordConfirm} = InputHandler.tests;
    let input = new InputHandler(password, passwordConfirm);
    let id = {password: $("#password"), passwordConfirm: $("#passwordConfirm"),};

    id.password
        .on(input.EVENTS, function () {
            input.validPassword($(this), password);
        })
        .focus(function () {
            input.resetPassword($(this), id.passwordConfirm, password, passwordConfirm);
        })
        .change(function () {
            input.turnOnPasswordConfirm(id.passwordConfirm, password);
        });

    id.passwordConfirm
        .on(input.EVENTS, function () {
            input.confirmPassword($(this), id.password, passwordConfirm);
        });
});
