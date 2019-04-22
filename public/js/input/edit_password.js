"use strict";

$(document).ready(function () {
    let {passwordOld, password, passwordConfirm} = InputHandler.tests;
    let input = new InputHandler(passwordOld, password, passwordConfirm);
    let id = {passwordOld: $("#passwordOld"), password: $("#password"), passwordConfirm: $("#passwordConfirm")};

    id.passwordOld
        .on(input.EVENTS, function () {
            input.validPassword($(this), passwordOld);
        });

    id.password
        .on(input.EVENTS, function () {
            input.validPassword($(this), password, true);
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

    $("input").change(function () {
        console.log(input.testObj);
    })
});
