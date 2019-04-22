"use strict";

/**
 * This class handles time and date issues.
 */
class TimeHandler {
    /**
     * Constructor function for TimeHandler. Initiates the class's vars and constants.
     */
    constructor() {
        // Date and time constants.
        this.MS_IN_SEC = 1000;
        this.SEC_IN_HOUR = 3600;
        this.MIN_IN_HOUR = 60;
        this.MIN_IN_HALF_HOUR = 30;
        this.HOURS_IN_DAY = 24;
        this.DOUBLE_DIGITS = 10;
        this.DAY_NAMES = ["ראשון", "שני", "שלישי", "רביעי", "חמישי", "שישי", "שבת"];
        this.WEEK_REF = ["היום", "מחר", "מחרתיים"];
        this.now = new Date(); // Assign var with Date object of current time and date.
    }

    /**
     * Creates formatted string of current date from Date object in form of: YYYY-MM-DD.
     * @returns {string} representing the date right now.
     */
    dateNow() {
        // Formats the month and day to double-digit string.
        let month = this._doubleDigit(this.now.getMonth() + 1);
        let day = this._doubleDigit(this.now.getDate());
        return `${this.now.getFullYear()}-${month}-${day}`
    }

    /**
     * Creates HTML select to choose time of day in 30 minutes intervals.
     * @returns {string} of HTML select element.
     */
    createTimeSelect() {
        let resultHTML = "";
        // Assign var with current time formatted to 30 minutes interval.
        let timeNow = this._timeNow();
        for (let hour = 0; hour < this.HOURS_IN_DAY; hour++)
            // For each 30 minutes in the day creates select option.
            for (let min = 0; min < this.MIN_IN_HOUR; min += this.MIN_IN_HALF_HOUR) {
                let val = this._timeStr(hour, min);
                // If this time is the current time selects it as the default option.
                let select = val === timeNow ? "selected" : "";
                resultHTML += `<option value="${val}" ${select}>${val}</option>`;
            }
        return resultHTML;
    }

    /**
     * Calculates arrival time regarding departure time and trip duration.
     * @param depTime {object} Date. Containing specified departure time.
     * @param tripDuration {object} Date. Containing trip duration in minutes.
     * @returns {string} of arrival time.
     */
    arrivalTime(depTime, tripDuration) {
        // Assign total trip hours and minutes.
        let hourTotal = depTime.getHours() + tripDuration.getHours();
        let minTotal = depTime.getMinutes() + tripDuration.getMinutes();
        // Adds the minutes that are above 60 to hours.
        let addedHours = Math.floor(minTotal / this.MIN_IN_HOUR);
        let hour = (hourTotal + addedHours) % this.HOURS_IN_DAY;
        // Subtracts the minutes that are above 60 from minutes.
        let min = minTotal - addedHours * this.MIN_IN_HOUR;
        return this._timeStr(hour, min);
    }

    /**
     * Generate departure's (1) day name (sunday, monday...); (2) week reference if relevant (today, tomorrow...)
     * (3) date string in form of: DD.MM.
     * @param depDate {object} Date. Containing specified departure date.
     * @returns {object} of day name, week reference and date string.
     */
    dayStr(depDate) {
        // Assign Date object with current date, and sets time to 00:00:00.
        let today = new Date();
        today.setHours(0, 0, 0, 0);
        // Calculate how many days differ between departure date and current date.
        let diffTemp = Math.abs(depDate.getTime() - today.getTime());
        let diff = Math.round(diffTemp / (this.MS_IN_SEC * this.SEC_IN_HOUR * this.HOURS_IN_DAY));
        return {
            name: this.DAY_NAMES[depDate.getDay()], // Assign relevant day name from DAY_NAMES array.
            ref: diff < this.WEEK_REF.length ? this.WEEK_REF[diff] : null, // Assign relevant week reference, if any.
            date: `${depDate.getDate()}.${depDate.getMonth() + 1}` // Assign date string.
        }
    }

    /**
     * Converts trip duration seconds to time string in form of: HH:MM.
     * @param sec {int} of seconds to convert.
     * @returns {string} of formatted time string.
     */
    secToTimeStr(sec) {
        let totalTime = sec / this.SEC_IN_HOUR; // Assign var with hour float representation of total seconds.
        let hour = Math.floor(totalTime); // Rounds down the float to the hours.
        let min = Math.round((totalTime % 1) * this.MIN_IN_HOUR); // Multiples remaining fracture with minutes in hour.
        return this._timeStr(hour, min);
    }

    /**
     * Formats time numbers to time string.
     * @param hour {int} of hour in range 0-23.
     * @param min {int} of minutes in range 0-59.
     * @returns {string} of formatted time string in form of: HH:MM.
     * @private
     */
    _timeStr(hour, min) {
        return `${this._doubleDigit(hour)}:${this._doubleDigit(min)}`;
    }

    /**
     * Creates formatted string of current time from Date object in form of: HH:00/HH:30.
     * @returns {string} representing the time right now.
     * @private
     */
    _timeNow() {
        let hour = (this.now.getHours()) % this.HOURS_IN_DAY; // Assign hour var in range 0-23.
        let min = (this.now.getMinutes() + 1) % this.MIN_IN_HOUR; // Assign minute var in range 0-59.
        // Rounds minute to fit 0/30 minutes format and change hour accordingly.
        if (min < this.MIN_IN_HALF_HOUR)
            min = this.MIN_IN_HALF_HOUR;
        else {
            min = 0;
            hour++;
        }
        return this._timeStr(hour, min);
    }

    /**
     * Reformat date/time string to appear as double-digit string (e.g. 1:4 -> 01:04 / 2-3 -> 02-03).
     * @param num {int} of specified time.
     * @returns {string/null} of reformatted time string, or null if input not valid.
     * @private
     */
    _doubleDigit(num) {
        if (!Number.isInteger(num)) return null; // Input is not int, return null.
        else if (num < this.DOUBLE_DIGITS) return `0${num}`; // If number < 10, prepends '0'.
        return num.toString();
    }
}
