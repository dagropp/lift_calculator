"use strict";

/**
 * This class generates custom messages regarding user's specified drive.
 */
class DriveMassage {
    /**
     * Constructor function for DriveMassage. Initiates the class's vars according to the drive details.
     * @param sessionObj {object} fetched from PHP server containing drive details.
     */
    constructor(sessionObj) {
        if (typeof sessionObj !== "object") throw "No session object."; // Given object not valid, throw exception.
        // Checks if any of the object fields is undefined, if so throws exception.
        for (let field of Object.values(CostObj)) {
            if (typeof field === "undefined")
                throw "Invalid field in object.";
        }
        // Relevant inputs RegEx tests.
        this.REGEX = {
            date: /^\d{4}-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])$/,
            time: /^[0-2][0-9]:[0-5][0-9]$/
        };
        // Drive details initiated on class construction.
        this.firstName = sessionObj.firstName;
        this.lastName = sessionObj.lastName;
        this.phoneNum = sessionObj.phoneNum;
        this.origin = sessionObj.origin;
        this.destination = sessionObj.destination;
        this.duration = new Date(`01/01/1970 ${sessionObj.duration}`);
        this.tolls = sessionObj.tolls;
        this.cost = sessionObj.cost;
        this.timeHandler = new TimeHandler(); // Construct TimeHandler object to handle time and date issues.
        // Drive date and time details re-initiated and changed on each message creation.
        this.depDate = undefined;
        this.depTime = undefined;
        this.arrTime = undefined;
        this.weekDay = undefined;
        this.weekRef = undefined;
        this.dateRef = undefined;
        this.timeRef = undefined;
    }

    /**
     * Package function that calls functions that handle date and time issues, and returns the chosen message type.
     * @param date {string} of drive departure date.
     * @param time {string} of drive departure time.
     * @param type {int} with type of message chosen.
     * @returns {string} of chosen message type.
     */
    generateMsg(date, time, type) {
        if (!Number.isInteger(type)) throw "Invalid message type."; // If type not int throws exception.
        this._setDepDateTime(date, time); // calls function to handle all date and time issues.
        let msgTypes = [this._regularMsg(), this._casualMsg(), this._formalMsg()];
        return msgTypes[type];
    }

    /**
     * Handle all date and time issues, and called on each message creation.
     * @param date {string} of drive departure date.
     * @param time {string} of drive departure time.
     * @private
     */
    _setDepDateTime(date, time) {
        if (!this.REGEX.date.test(date)) throw "Invalid date."; // If date not in format (YYYY-MM-DD), throw exception.
        if (!this.REGEX.time.test(time)) throw "Invalid time."; // If time not in format (HH:MM), throw exception.
        this.depDate = new Date(date); // Creates Date object for departure date.
        this.timeRef = time; // Departure time in string format.
        this.depTime = new Date(`01/01/1970 ${time}`); // Departure time as a Date object.
        // Calculates arrival time based on departure time and trip duration.
        this.arrTime = this.timeHandler.arrivalTime(this.depTime, this.duration);
        let dayStr = this.timeHandler.dayStr(this.depDate);
        this.weekDay = dayStr.name; // Departure as day name.
        this.dateRef = dayStr.date; // Departure as readable date.
        this.weekRef = dayStr.ref ? dayStr.ref : `ביום ${this.weekDay}`; // Departure as week reference.
    }

    /**
     * Splits place string to object containing street address and city name (if not the same).
     * @param place {string} of place to separate.
     * @returns {object} of separated street address and city.
     * @private
     */
    _separatePlace(place) {
        let result = place.split(",").map(field => field.trim()); // Splits the string to array.
        return {street: result[0], city: result[result.length - 1]}
    }

    /**
     * Generates regular message with the drive details.
     * @returns {string} of the generated message.
     * @private
     */
    _regularMsg() {
        let origin = this._separatePlace(this.origin);
        let destination = this._separatePlace(this.destination);
        return `
            מוזמנים ומוזמנות להצטרף אלי לנסיעה מ${origin.city} ל${destination.city}.<br>
            נצא ${this.weekRef} בשעה ${this.timeRef} מ${origin.street}
            ונגיע ל${this.destination} סביב ${this.arrTime}.<br>
            ניסע ${this.tolls}.
            השתתפות ${this.cost} ש"ח.<br>
            ${this.firstName}, ${this.phoneNum}. 
            `;
    }

    /**
     * Generates casual message with the drive details.
     * @returns {string} of the generated message.
     * @private
     */
    _casualMsg() {
        let origin = this._separatePlace(this.origin);
        let destination = this._separatePlace(this.destination);
        return `
            הי כולם!<br>
            בואו תיסעו איתי ${this.weekRef} מ${origin.city} ל${destination.city}.<br>
            נצא ב-${this.timeRef} מ${origin.street} וניסע ${this.tolls}.<br>
            מוזמנים ומוזמנות להצטרשף (${this.cost} ש"ח).<br>
            דברו איתי - ${this.firstName} ${this.phoneNum}.
            `;
    }

    /**
     * Generates formal message with the drive details.
     * @returns {string} of the generated message.
     * @private
     */
    _formalMsg() {
        let origin = this._separatePlace(this.origin);
        let destination = this._separatePlace(this.destination);
        return `
            שלום רב,<br>
            מוזמנים ומוזמנות להצטרף אלי לנסיעה מ${origin.city} ל${destination.city}.<br>
            אצא ${this.weekRef}, ה-${this.dateRef}, בשעה ${this.timeRef} מ${origin.street}.<br>
            הגעה משוערת ל${this.destination} בשעה ${this.arrTime}.<br>
            הנסיעה ${this.tolls},
            ובהשתתפות הוצאות בסך ${this.cost} ש"ח.<br>
            לפרטים נוספים:<br>
            ${this.firstName} ${this.lastName}, ${this.phoneNum}. 
            `;
    }
}
