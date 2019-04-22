"use strict";

$(document).ready(function () {
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
    let textInputs = {
        email: {id: id.email, profileVal: profile.email, field: "email"},
        password: {id: id.password, profileVal: "", field: "password"},
        passwordConfirm: {id: id.passwordConfirm, profileVal: "", field: "passwordConfirm"},
        firstName: {id: id.firstName, profileVal: profile.firstName, field: "firstName"},
        lastName: {id: id.lastName, profileVal: profile.lastName, field: "lastName"},
        phoneNum: {id: id.phoneNum, profileVal: profile.phoneNum.replace("-", ""), field: "phoneNum"},
    };

    assignEvents(textInputs);

    function assignEvents(obj) {
        for (let field of Object.values(obj)) {
            field.id
                .val(field.profileVal)
                .blur(function () {
                    if (!field.id.val())
                        field.id.val(field.profileVal);
                });
        }
    }

});
