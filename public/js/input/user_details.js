"use strict";

$(document).ready(function () {
    let edit = typeof inputEdit !== "undefined";
    let {email, password, passwordConfirm, firstName, lastName, phoneNum, car} = InputHandler.tests;
    let input = edit
        ? new InputHandler(email, firstName, lastName, phoneNum, car)
        : new InputHandler(email, password, passwordConfirm, firstName, lastName, phoneNum, car);
    let id = {
        email: $("#email"),
        password: $("#password"),
        passwordConfirm: $("#passwordConfirm"),
        firstName: $("#firstName"),
        lastName: $("#lastName"),
        phoneNum: $("#phoneNum"),
        carCompanyModel: $("#carCompany, #carModel"),
        carYears: $("#carYears"),
        reset: $("#reset")
    };

    if (edit) {
        let profile = userProfile;
        var textInputs = {
            email: {id: id.email, profileVal: profile.email, field: "email"},
            firstName: {id: id.firstName, profileVal: profile.first_name, field: "firstName"},
            lastName: {id: id.lastName, profileVal: profile.last_name, field: "lastName"},
            phoneNum: {id: id.phoneNum, profileVal: profile.phone_num.replace("-", ""), field: "phoneNum"},
        };
        editAssignInputs(textInputs);
        editAssignEvents(textInputs);
        editValidateAllInputs();
    }

    id.email
        .on(input.EVENTS, function () {
            input.validEmail($(this), email);
        });

    id.password
        .on(input.EVENTS, function () {
            input.validPassword($(this), password, true);
        })
        .focus(function () {
            input.resetPassword($(this), id.passwordConfirm, password, passwordConfirm);
        })
        .blur(function () {
            // if (input.testObj[password].state)
            //     input._inputState(id.passwordConfirm, true);
            input.turnOnPasswordConfirm(id.passwordConfirm, password);
        });

    id.passwordConfirm
        .on(input.EVENTS, function () {
            input.confirmPassword($(this), id.password, passwordConfirm);
        });

    id.firstName
        .on(input.EVENTS, function () {
            input.validName($(this), firstName);
        });

    id.lastName
        .on(input.EVENTS, function () {
            input.validName($(this), lastName);
        });

    id.phoneNum
        .keypress(function (e) {
            if (e.which < 48 || e.which > 57) {
                e.preventDefault();
            }
        })
        .on(input.EVENTS, function () {
            input.validPhone($(this), phoneNum);
        });

    id.carCompanyModel
        .change(function () {
            input.resetTest(id.carYears, car)
        });

    id.carYears
        .change(function () {
            input.validCar($(this), car);
        });

    id.reset
        .click(function (e) {
            input.resetFields($("p"));
            if (edit) {
                e.preventDefault();
                editAssignInputs(textInputs);
                editValidateAllInputs();
            }
        });

    function editAssignInputs(obj) {
        for (let field of Object.values(obj))
            field.id
                .val(field.profileVal)
    }

    function editAssignEvents(obj) {
        for (let field of Object.values(obj))
            field.id
                .blur(function () {
                    if (!field.id.val())
                        field.id.val(field.profileVal);
                })
                .focus(function () {
                    field.id.val("");
                })
    }

    function editValidateAllInputs() {
        if (!id.carYears.val())
            return setTimeout(editValidateAllInputs, 50);
        input.validEmail(id.email, email);
        input.validName(id.firstName, firstName);
        input.validName(id.lastName, lastName);
        input.validPhone(id.phoneNum, phoneNum);
        input.validCar(id.carYears, car);
    }
});
