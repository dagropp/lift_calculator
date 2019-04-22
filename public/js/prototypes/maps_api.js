"use strict";

/**
 * This class handles the use of Google Maps API: Places auto-complete and Distance matrix.
 */
class MapsAPI {

    /**
     * Initiates places auto-complete for given input ID.
     * @param elementID {string} of input ID.
     */
    setAutoComplete(elementID) {
        // Bind API's auto-complete to the specified ID.
        let autoComplete =
            new google.maps.places.Autocomplete((document.getElementById(elementID)), {types: ['geocode']});
        autoComplete.setComponentRestrictions({country: 'il'}); // Restricts the API's auto-complete to Israel.
    }

    /**
     * Gather the parameters to calculate distance and duration between 2 points and calls callback function.
     * @param origin {string} of route origin place.
     * @param destination {string} of route destination place.
     * @param avoidTolls {boolean} true if avoids toll roads, false if otherwise.
     */
    getDistance(origin, destination, avoidTolls) {
        let service = new google.maps.DistanceMatrixService();
        service.getDistanceMatrix(
            {
                origins: [origin],
                destinations: [destination],
                travelMode: 'DRIVING',
                unitSystem: google.maps.UnitSystem.METRIC,
                avoidHighways: false,
                avoidTolls: avoidTolls,
            }, this._callbackDistance);
    }

    /**
     * Callback function that calculates distance and duration between 2 points, assigns it to inputs and submits form.
     * @param response {object} API response.
     * @param status {string} containing request status.
     */
    _callbackDistance(response, status) {
        if (status === "OK") {
            let time = new TimeHandler();
            let results = response.rows[0].elements[0];
            let searchStatus = (results.status === "OK");
            // If status is OK assign var with float val of distance in KM. If not, assign null.
            let distance = searchStatus ? (results.distance.value / 1000).toFixed(1) : null;
            // If status is OK assign var with time string containing trip duration. If not, assign null.
            let duration = searchStatus ? time.secToTimeStr(results.duration.value) : null;
            // Assign the values to the relevant inputs and submit form.
            $("#distance").val(distance);
            $("#duration").val(duration);
            $("#new_drive_form").submit();
        }
    }
}
