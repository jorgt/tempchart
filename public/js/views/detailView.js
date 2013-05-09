define([
    'knockout',
    'views/calendarView',
    'views/dayView',
    'views/chartView'
], function(ko, cal, day, chart) {
    "use strict";

    return function() {
        ko.applyBindings(new cal(), document.getElementById('calendar'));
        ko.applyBindings(new day(), document.getElementById('day-details'));
        ko.applyBindings(new chart('graph'), document.getElementById('graph'));
    };
});