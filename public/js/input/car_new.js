"use strict";

const YEAR_DIGITS = 4;

$(document).ready(function () {
    let id = {
        carCompany: $("#carCompany"),
        carModel: $("#carModel"),
        carModelNew: $("#carModelNew"),
        carYearsNew: $("#carYearsNew"),
        carGasType: $("#carGasType"),
        carKPL: $("#carKPL"),
        newCarFieldsButtons: $(".newCarFields"),
        addCarButton: $("#addCar")
    };
    let {carCompany, carModel, carYears, carGasType, carKPL} = InputHandler.tests;
    let input = new InputHandler(carCompany, carModel, carYears, carGasType, carKPL);
    input.HTML.submitForm = id.addCarButton;
    let hyphen = false;
    let toggleCounter = 0;

    id.newCarFieldsButtons
        .click(toggleViews);

    id.carCompany
        .change(function () {
            input.validCar($(this), carCompany);
        });

    id.carModel
        .change(function () {
            input.validCar($(this), carModel);
        });

    id.carModelNew
        .on(input.EVENTS, function () {
            input.validCarName($(this), carModel);
        });

    id.carYearsNew
        .keypress(function (e) {
            if (e.which < 48 || e.which > 57)
                e.preventDefault();
        })
        .on(input.EVENTS, function () {
            input.validCarYears($(this), carYears);
            let val = $(this).val();
            if (val.length >= 4 && !hyphen) {
                if (!val.includes("-"))
                    addHyphen($(this));
                hyphen = true;
            }
            if (val.length < 4 && hyphen)
                hyphen = false;
        });

    id.carGasType
        .change(function () {
            input.validCarGasType($(this), carGasType);
        });

    id.carKPL
        .keypress(function (e) {
            let val = $(this).val();
            let isFloat = val.includes(".");
            if (!isFloat)
                $(this).attr("maxlength", false);
            if (e.which < input.CHAR_CODES.firstNum || e.which > input.CHAR_CODES.lastNum)
                if (e.which === input.CHAR_CODES.dot && !isFloat)
                    $(this).attr("maxlength", val.length + 2);
                else
                    e.preventDefault();
        })
        .on(input.EVENTS, function () {
            input.validCarKPL($(this), carKPL);
        });

    function toggleViews() {
        toggleCounter++;
        let newYears = toggleCounter % 2 !== 0;
        id.newCarFieldsButtons.attr("disabled", false);
        $(this).attr("disabled", true);
        id.carModel.toggle();
        id.carModelNew.toggle();
        id.carGasType.attr("disabled", newYears);
        input.resetTest(id.carCompany, carCompany, true);
        input.resetTest(id.carModel, carModel, true);
        input.resetTest(id.carModelNew, carModel, true);
        input.resetTest(id.carYearsNew, carYears, true);
        input.resetTest(id.carGasType, carGasType, true);
        input.resetTest(id.carKPL, carKPL, true);
        input.validCarGasType(id.carGasType, carGasType);
    }

});

function addHyphen(input) {
    let val = input.val();
    let year1 = val.substring(0, YEAR_DIGITS);
    let year2 = val.substring(YEAR_DIGITS);
    input.val(`${year1}-${year2}`);
}
