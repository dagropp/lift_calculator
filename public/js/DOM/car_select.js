"use strict";

// Base selection message constants.
const BASE = {car: "הרכב", company: "בחירת חברת", model: "בחירת הדגם של", years: "בחירת טווח השנים של"};
let edit = typeof userProfile !== "undefined"; // Profile edit if userProfile object is set.
// User's car details if edit profile, base selection otherwise.
let original = {
    company: edit ? userProfile.car.str.company : BASE.car,
    model: edit ? userProfile.car.str.model : BASE.car,
    years: edit ? userProfile.car.str.yearRange : BASE.car
};

// Start of jQuery code.
$(document).ready(function () {
    let carID = {company: $("#carCompany"), model: $("#carModel"), years: $("#carYears")};
    // Get cars re-generated JSON and and use the data for the dynamic select.
    $.getJSON("../../temp_files/general/cars.json", function (carsJSON) {
        carID.company
            .html(companyMenu()) // Initiate default company select.
            .change(function () {
                carID.model.html(modelMenu($(this).val()));
                carID.years.html(yearsMenu());
            });
        carID.model
            .html(modelMenu(original.company)) // Initiate default empty model select.
            .change(function () {
                let companyVal = carID.company.val();
                carID.years.html(yearsMenu(companyVal, $(this).val()));
            });
        carID.years
            .html(yearsMenu(original.company, original.model)); // Initiate default empty years select.

        function companyMenu() {
            let baseMsg = `${BASE.company} ${BASE.car}`;
            let companyList = Object.keys(carsJSON);
            return createSelectMenu(baseMsg, companyList, original.company);
        }

        function modelMenu(company = BASE.car) {
            let baseMsg = `${BASE.model} ${company}`;
            if (company === BASE.car)
                return createSelectMenu(baseMsg);
            return createSelectMenu(baseMsg, Object.keys(carsJSON[company]), original.model);
        }

        function yearsMenu(company = BASE.car, model = BASE.car) {
            let baseMsg = `${BASE.years} ${model}`;
            if (model === BASE.car || company === BASE.car)
                return createSelectMenu(baseMsg);
            return createSelectMenu(baseMsg, Object.values(carsJSON[company][model]), original.years);
        }
    })
});

/**
 * Generates cars HTML select input.
 * @param baseMsg {string} message to display as select default.
 * @param list {array} array of companies/models/years if relevant, empty array (default) if only base message.
 * @param original {string} user's previously selected car if available, null (default) if otherwise.
 * @returns {string} of updated HTML select input.
 */
function createSelectMenu(baseMsg, list = [], original = BASE.car) {
    let HTML = `<option value=${BASE.car}>${baseMsg}</option>`; // Create default option with base message.
    // For each row, create a select option.
    for (let row of list) {
        let fieldSelect = row === original ? "selected" : ""; // if row is user's original car, assign as default.
        HTML += `<option value="${row}" ${fieldSelect}>${row}</option>`;
    }
    return HTML;
}