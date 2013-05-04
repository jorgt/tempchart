require.config({
    paths: {
        'jquery': 'vendor/jquery.2.min',
        'underscore': 'vendor/underscore-min',
        'knockout': 'vendor/knockout', //knockout-debug
        'flotr': 'vendor/flotr.amd',
        'bean': 'vendor/bean-min',
        'sammy': 'vendor/sammy',
        "moment": "vendor/moment"
    },
    shim: {
        'jquery': {
            deps: [],
            exports: '$'
        },
        'sammy': {
            deps: ['jquery'],
            exports: 'Sammy'
        },
        'underscore': {
            exports: '_'
        }
    }
});

define([
    'controllers/navigation',
    'views/calendarView',
    'views/dayView',
    'views/chartView',
    'views/titleView',
    'knockout'
], function(nav, cal, day, chart, title, ko) {


    //this applies all bindings for the calendar, and feeds it the
    //global observable on Date, used by the calendar to update the graph
    ko.applyBindings(new cal(), document.getElementById('calendar'));
    ko.applyBindings(new day(), document.getElementById('day-details'));
    ko.applyBindings(new chart('graph'), document.getElementById('graph'));
    ko.applyBindings(new title(), document.getElementById('title'));

    //start the app at today's date by setting the observable. 
    nav.run('#/today');



});