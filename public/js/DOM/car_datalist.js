"use strict";

// Base selection message constants.
const BASE = "הרכב";
let {carCompany, carModel, carYears, carGasType, carKPL} = InputHandler.tests;
let inputHandler = new InputHandler(carCompany, carModel, carYears);

$(document).ready(function () {
    let input = {company: $("#carCompany"), model: $("#carModel"), years: $("#carYears")};
    let dataList = {company: $("#carCompanyList"), model: $("#carModelList"), years: $("#carYearsList")};
    let baseMsg = {
        company: {id: input.company, placeholder: "בחירת חברת"},
        model: {id: input.model, placeholder: "בחירת הדגם של"},
        years: {id: input.years, placeholder: "בחירת טווח השנים של"},
    };
    inputHandler.resetInputs(...Object.values(input));
    inputHandler.resetFields(...Object.values(dataList));
    resetPlaceHolder(...Object.values(baseMsg));

    $.getJSON("temp_files/general/cars.json", function (carsJSON) {
        dataList.company
            .html(createDataList(Object.keys(carsJSON)));

        input.company
            .focus(function () {
                inputHandler.resetFields(dataList.model, dataList.years);
                inputHandler.resetInputs(...Object.values(input));
                resetPlaceHolder(baseMsg.model, baseMsg.years);
                newInput(false, ...Object.values(input))
            })
            .change(function () {
                let val = $(this).val();
                if (Object.keys(carsJSON).includes(val)) {
                    dataList.model.html(createDataList(Object.keys(carsJSON[val])));
                    updatePlaceHolder(baseMsg.model, val)
                } else newInput(true, $(this));
            });

        input.model
            .focus(function () {
                inputHandler.resetFields(dataList.years);
                inputHandler.resetInputs(input.model, input.years);
                resetPlaceHolder(baseMsg.years);
                newInput(false, input.model, input.years);
            })
            .change(function () {
                let companyVal = input.company.val();
                let thisVal = $(this).val();
                let companyExist = typeof carsJSON[companyVal] !== "undefined";
                let modelExist = companyExist ? Object.keys(carsJSON[companyVal]).includes(thisVal) : false;
                if (modelExist) {
                    dataList.years.html(createDataList(carsJSON[companyVal][thisVal]));
                    updatePlaceHolder(baseMsg.years, thisVal)
                } else newInput(true, $(this));
            });

        input.years
            .focus(function () {
                inputHandler.resetInputs(input.years);
                newInput(false, input.years);
            })
            .change(function () {
                let companyVal = input.company.val();
                let modelVal = input.model.val();
                let thisVal = $(this).val();
                let companyExist = typeof carsJSON[companyVal] !== "undefined";
                let modelExist = companyExist ? typeof carsJSON[companyVal][modelVal] !== "undefined" : false;
                let yearsExist = modelExist ? carsJSON[companyVal][modelVal].includes(thisVal) : false;
                newInput(!yearsExist, $(this));
            });
    });
});

function createDataList(list) {
    let HTML = "";
    for (let row of list)
        HTML += `<option value="${row}">${row}</option>`;
    return HTML;
}

function updatePlaceHolder(inp, change) {
    inp.id.attr("placeholder", `${inp.placeholder} ${change}`);
}

function resetPlaceHolder(...inputs) {
    for (let inp of inputs)
        inp.id.attr("placeholder", `${inp.placeholder} ${BASE}`);
}

function newInput(bool, ...inputs) {
    let color = bool ? "red" : "black";
    for (let inp of inputs)
        inp.css("color", color);
}
