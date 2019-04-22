"use strict";

let routeHistory = {
    origin: null,
    destination: null,
    tollRoads: [],
    avoidTolls: true
};
let avoidTolls = true;

$(document).ready(function () {

    let map = new MapsAPI();

    $("#origin, #destination")
        .val("")
        .focus(function () {
            let id = $(this).attr("id");
            map.setAutoComplete(id);
        });

    $("input[type=checkbox]")
        .change(function () {
            let inputName = $(this).attr("name");
            if (inputName == "tollRoads[]") {
                let value = $(this).val();
                let idx = routeHistory.tollRoads.indexOf(value);
                if (idx != -1)
                    routeHistory.tollRoads.splice(idx);
                else
                    routeHistory.tollRoads.push(value);
            }
            avoidTolls = !routeHistory.tollRoads.length > 0;
        });

    $("#new_drive_form")
        .find("button")
        .click(function () {
            let origin = $("#origin").val();
            let destination = $("#destination").val();
            $("#originH").val(origin);
            $("#destinationH").val(destination);
            if (origin != "" && destination != "") {
                if (origin != routeHistory.origin ||
                    destination != routeHistory.destination ||
                    avoidTolls != routeHistory.avoidTolls) {
                    map.getDistance(origin, destination, avoidTolls);
                    routeHistory.origin = origin;
                    routeHistory.destination = destination;
                    routeHistory.avoidTolls = avoidTolls;
                }
            }
        });

    $("#passNum")
        .html(createPassNumSelect(10));

    $("h2.drop_down").click(function () {
        $(this).next().slideToggle(300);
        $(this).next().siblings("section.hide").slideUp(300);
    });


    if (typeof CostObj == "object") {
        try {
            let driveMsg = new DriveMassage(CostObj);

            $("#driveMsg")
                .prop("hidden", false)
                .find("button")
                .click(function () {
                    let date = $("#depDate").val();
                    let time = $("#depTime").val();
                    let type = parseInt($("#msgType").val());
                    $("#msgDisplay").html(driveMsg.generateMsg(date, time, type));
                });

            let time = new TimeHandler();
            let today = time.dateNow();
            $("#depDate")
                .val(today)
                .prop("min", today);

            $("#depTime")
                .html(time.createTimeSelect())

        } catch (e) {
            console.log(e)
        }
    }
});

function createPassNumSelect(max) {
    let resultHTML = "";
    for (let num = 1; num <= max; num++) {
        resultHTML += `<option value=${num} ${num === 2 ? "selected" : ""}>${num}</option>`;
    }
    return resultHTML;
}
