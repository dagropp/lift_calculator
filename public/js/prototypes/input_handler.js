"use strict";

/**
 * This class handles HTML inputs validation in the various forms in the app.
 */
class InputHandler {
    /**
     * Constructor function for InputHandler. Creates the test object and initiates the class's vars and constants.
     * @param tests {...string} of input types to test.
     */
    constructor(...tests) {
        // Class constants: default string, inputs lengths, indication symbols and jQuery events to bind to elements.
        this.EMPTY = "";
        this.MIN_PASSWORD = 8;
        this.MIN_NAME = 2;
        this.CAR_BASE = "הרכב";
        this.MSG_NOT = "<i class='fas fa-times' style='color: pink'></i>";
        this.MSG_OK = "<i class='fas fa-check' style='color: green'></i>";
        this.EVENTS = "keyup keydown keypress blur change click";
        // All relevant inputs RegExp tests.
        this.REGEX = {
            lowerCase: /[a-z]/,
            capitals: /[A-Z]/,
            numbers: /[0-9]/,
            whiteSpace: /^\S*$/,
            email: /^([\w-.]+@([\w-]+\.)+[\w-]{2,4})?$/,
            english: /^[a-zA-Z]+$/,
            hebrew: /^[א-ת\s]+$/,
            phone: /^05\d{8}$/,
            int: /^\d+$/,
            float: /\d\.\d$/,
            carName: /^[-/+ša-z0-9\u00C0-\u00D6\u00D8-\u00f6\u00f8-\u00ff\s]+$/i,
            carYears: {half: /^\d{4}-$/, full: /^\d{4}-\d{4}$/},
        };
        this.CHAR_CODES = {
            firstNum: 48,
            lastNum: 57,
            dot: 46
        };
        this.HTML = {p: $("p"), submitForm: $("#submitForm")}; // All relevant documents elements.
        this.testObj = this._createTestObj(...tests); // Generate test object of input types to test.
    }

    /**
     * Checks if input's value is a valid email address, and updates its validity indicators accordingly.
     * @param element {object} jQuery of triggered element.
     * @param field {string} of relevant input field in test object.
     */
    validEmail(element, field) {
        let val = element.val();
        // Checks if input is not empty and matches email RegExp test. Assign boolean result to field state.
        this.testObj[field].state = this._validInput(val) && this._regexTest(val, this.REGEX.email);
        this._updateIndicators(element, field); // Updates input's validity indicators.
    }

    /**
     * Checks if input's value is a valid password (and secure), and updates its validity indicators accordingly.
     * @param element {object} jQuery of triggered element.
     * @param field {string} of relevant input field in test object.
     * @param secure {boolean} true if password should be secure, false (default) if otherwise.
     */
    validPassword(element, field, secure = false) {
        let val = element.val();
        let length = this._validLength(val, this.MIN_PASSWORD); // Checks if input fits the minimum password length.
        let {lowerCase, capitals, numbers, whiteSpace} = this.REGEX; // Destructs REGEX object to the relevant tests.
        // If secure=true, checks if input matches the specified RegExp tests.
        let isSecure = secure ? this._regexTest(val, lowerCase, capitals, numbers, whiteSpace) : true;
        this.testObj[field].state = length && isSecure; // Assign boolean result of length and security to field state.
        this._updateIndicators(element, field); // Updates input's validity indicators.
    }

    /**
     * Checks if input's value matches the password, and updates its validity indicators accordingly.
     * @param confElement {object} jQuery of password confirm element.
     * @param passElement {object} jQuery of password element.
     * @param field {string} of relevant input field in test object.
     */
    confirmPassword(confElement, passElement, field) {
        let confVal = confElement.val();
        let passVal = passElement.val();
        // Checks if input's value matches password input. Assign boolean result to field state.
        this.testObj[field].state = passVal === confVal;
        this._updateIndicators(confElement, field); // Updates input's validity indicators.
    }

    /**
     * Checks if input's value is a valid name (in Hebrew), and updates its validity indicators accordingly.
     * @param element {object} jQuery of triggered element.
     * @param field {string} of relevant input field in test object.
     */
    validName(element, field) {
        let val = element.val();
        // Checks if input matches name RegExp test and fits minimum name length. Assign boolean result to field state.
        this.testObj[field].state = this._regexTest(val, this.REGEX.hebrew) && this._validLength(val, this.MIN_NAME);
        this._updateIndicators(element, field); // Updates input's validity indicators.
    }

    /**
     * Checks if input's value is a valid phone number (05XXXXXXXX), and updates its validity indicators accordingly.
     * @param element {object} jQuery of triggered element.
     * @param field {string} of relevant input field in test object.
     */
    validPhone(element, field) {
        let val = element.val();
        // Checks if input is not empty and matches phone number RegExp test. Assign boolean result to field state.
        this.testObj[field].state = this._validInput(val) && this._regexTest(val, this.REGEX.phone);
        this._updateIndicators(element, field); // Updates input's validity indicators.
    }

    /**
     * Async function that checks if input's value is a valid Maps API place,
     * and updates its validity indicators accordingly.
     * @param element {object} jQuery of triggered element.
     * @param field {string} of relevant input field in test object.
     * @returns {Promise<void>}
     */
    async validLocation(element, field) {
        this._locationStatus(element); // Calls asynchronous function that checks element place status in the API.
        // Awaits for the boolean result from _locationStatus() function. Assign result to field state.
        this.testObj[field].state = await InputHandler.asyncLocation;
        this._updateIndicators(element, field); // Updates input's validity indicators.
    }

    /**
     * Helper function for validLocation(). Uses Google Maps API to see if given location has valid place ID.
     * @param element {object} jQuery of triggered element.
     * @private
     */
    _locationStatus(element) {
        let val = element.val();
        if (this._validInput(val)) {
            let geoCoder = new google.maps.Geocoder(); // Using the API to geo-code input value.
            geoCoder.geocode({address: val}, function (results, status) {
                InputHandler.asyncLocation = status === "OK"; // Assign class static var the status of the request.
            });
        }
    }

    /**
     * Checks if input represents valid car object from the database, and updates its validity indicators accordingly.
     * @param element {object} jQuery of triggered element.
     * @param field {string} of relevant input field in test object.
     */
    validCar(element, field) {
        let val = element.val();
        // Checks if input is not the default selection. Assign boolean result to field state.
        this.testObj[field].state = val !== this.CAR_BASE;
        this._updateIndicators(element, field); // Updates input's validity indicators.
    }

    validCarName(element, field) {
        let val = element.val();
        // Checks if input is not empty and matches car name RegExp test. Assign boolean result to field state.
        this.testObj[field].state = this._validInput(val) && this._regexTest(val, this.REGEX.carName);
        this._updateIndicators(element, field); // Updates input's validity indicators.
    }

    validCarYears(element, field) {
        let val = element.val();
        // Checks if input matches any car years RegExp test. Assign boolean result to field state.
        this.testObj[field].state =
            this._regexTest(val, this.REGEX.carYears.full) || this._regexTest(val, this.REGEX.carYears.half);
        this._updateIndicators(element, field); // Updates input's validity indicators.
    }

    validCarGasType(element, field) {
        let val = element.val();
        // Checks if input is not the default selection, unless input disabled. Assign boolean result to field state.
        let isDisabled = element.prop("disabled");
        this.testObj[field].state = val != 0 || isDisabled; // ---make better... this sucks...
        this._updateIndicators(element, field); // Updates input's validity indicators.
    }

    validCarKPL(element, field) {
        let val = element.val();
        // Checks if input matches KPL int/float RegExp.
        let test = this._regexTest(val, this.REGEX.int) || this._regexTest(val, this.REGEX.float);
        this.testObj[field].state = this._validInput(val) && test; // Assign boolean test result to field state.
        this._updateIndicators(element, field); // Updates input's validity indicators.
    }

    /**
     * Resets input's validity indicators and state in the test object. Also clears the fields if specified.
     * @param element {object} jQuery of triggered element.
     * @param field {string} of relevant input field in test object.
     * @param clear {boolean} true to clear the chosen inputs, false (default) if otherwise.
     */
    resetTest(element, field, clear = false) {
        this.testObj[field].state = false; // Resets state in the test object.
        // If clear=true clears input field and its validity indicator.
        if (clear) {
            this.resetInputs(element);
            this.resetFields(this._nextP(element));
        }
        // clear=false - updates input's validity indicators.
        else
            this._updateIndicators(element, field);
    }

    /**
     * When triggered, calls resetTest(), with clear=true, for password and password confirm element.
     * @param passElement {object} jQuery of password element.
     * @param confElement {object} jQuery of password confirm element.
     * @param passField {string} of password input field in test object.
     * @param confField {string} of password confirm input field in test object.
     */
    resetPassword(passElement, confElement, passField, confField) {
        if (this._validInput(passElement.val())) {
            // Resets tests and clears the inputs.
            this.resetTest(passElement, passField, true);
            this.resetTest(confElement, confField, true);
            this._inputState(confElement, false); // Disables password confirm input.
        }
    }

    /**
     * Enables password confirm input, if password state in test object is true.
     * @param confElement {object} jQuery of password confirm element.
     * @param passField {string} of password input field in test object.
     */
    turnOnPasswordConfirm(confElement, passField) {
        this._inputState(confElement, this.testObj[passField].state);
    }

    /**
     * Clears multiple HTML elements.
     * @param elements {...object} jQuery, of HTML elements to clear.
     */
    resetFields(...elements) {
        for (let element of elements)
            element.html(this.EMPTY);
    }

    /**
     * Clears multiple HTML inputs' value.
     * @param inputs {...object} jQuery, of inputs to clear.
     */
    resetInputs(...inputs) {
        for (let input of inputs)
            input.val(this.EMPTY);
    }

    /**
     * Called on class construct to generate test object of input types to test.
     * @param tests {...string} of input types to test.
     * @returns {object/null} of generated test object, or null if no params were given on class construction.
     * @private
     */
    _createTestObj(...tests) {
        // No params were given. Test object is null.
        if (tests.length <= 0)
            return null;
        let result = {};
        // For each test input assigns default state and message.
        for (let test of tests)
            result[test] = {state: false, msg: this.EMPTY};
        return result;
    }

    /**
     * Checks if input is not empty.
     * @param input {string} of input value.
     * @returns {boolean} true if not empty, false if otherwise.
     * @private
     */
    _validInput(input) {
        return input !== this.EMPTY;
    }

    /**
     * Check if input fits minimum specified length.
     * @param input {string} of input value.
     * @param minLength {int} of minimum length.
     * @returns {boolean} true if input fits, false if otherwise.
     * @private
     */
    _validLength(input, minLength) {
        return input.length >= minLength;
    }

    /**
     * Checks if input matches (all) multiple RegExp tests.
     * @param input {string} of input value.
     * @param tests {...RegExp} of tests to perform.
     * @returns {boolean} true if all tests passed, false if at least 1 test failed.
     * @private
     */
    _regexTest(input, ...tests) {
        for (let test of tests)
            if (!test.test(input))
                return false;
        return true;
    }

    /**
     * Enables/disables HTML input.
     * @param element {object} jQuery of triggered element.
     * @param enabled {boolean} true to enable input, false to disable it.
     * @param focus {boolean} true (default) to focus on element, false if otherwise.
     * @private
     */
    _inputState(element, enabled, focus = true) {
        element.prop("disabled", !enabled);
        if (enabled && focus) element.focus();
    }

    /**
     * Package function that calls other functions to update input's validity indicators and form submit state.
     * @param element {object} jQuery of triggered element.
     * @param field {string} of relevant input field in test object.
     * @private
     */
    _updateIndicators(element, field) {
        this._indicatorMsg(element, field);
        this._submitState();
    }

    /**
     * Enables/disables form submit button, according to the states of the test object.
     * @private
     */
    _submitState() {
        let objFields = Object.values(Object.values(this.testObj));
        let stateArr = objFields.map(f => f.state); // Creates array from test object with state boolean values.
        // Checks if the test array includes false, and changes the submit button according to this result.
        this._inputState(this.HTML.submitForm, !stateArr.includes(false), false);
    }

    /**
     * Updates input's validity indicators, according to the tests performed.
     * @param element {object} jQuery of triggered element.
     * @param field {string} of relevant input field in test object.
     * @private
     */
    _indicatorMsg(element, field) {
            // Change message according to state and assign it to the relevant HTML element.
            this.testObj[field].msg = this.testObj[field].state ? this.MSG_OK : this.MSG_NOT;
            this._nextP(element).html(this.testObj[field].msg);
    }

    /**
     * Returns the next HTML paragraph.
     * @param element {object} jQuery of triggered element.
     * @returns jQuery {object} of next paragraph.
     * @private
     */
    _nextP(element) {
        return element.next(this.HTML.p);
    }
}

// InputHandler class static variables.
InputHandler.tests = {
    email: "email",
    password: "password",
    passwordConfirm: "passwordConfirm",
    passwordOld: "passwordOld",
    firstName: "firstName",
    lastName: "lastName",
    phoneNum: "phoneNum",
    car: "car",
    origin: "origin",
    destination: "destination",
    carCompany: "carCompany",
    carModel: "carModel",
    carYears: "carYears",
    carGasType: "carGasType",
    carKPL: "carKPL"
};
