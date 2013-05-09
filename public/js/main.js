require.config({
    paths: {
        'jquery': 'vendor/jquery.2.min',
        'underscore': 'vendor/underscore-min',
        'knockout': 'vendor/knockout', //knockout-debug
        'flotr': 'vendor/flotr.amd',
        'bean': 'vendor/bean-min',
        'sammy': 'vendor/sammy',
        'bootstrap': 'vendor/bootstrap.min',
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
        'bootstrap': {
            deps: ['jquery'],
            exports: '$'
        },
        'underscore': {
            exports: '_'
        }
    }
});

define([
    'controllers/navigation',
    'views/overviewView',
    'views/detailView',
    'views/titleView',
    'knockout'
], function(nav, overview, detail, title, ko) {


    //this applies all bindings for the calendar, and feeds it the
    //global observable on Date, used by the calendar to update the graph
    new detail();
    ko.applyBindings(new title(), document.getElementById('header'));
    ko.applyBindings(new overview('graph-overview'), document.getElementById('overview'));
//ko.applyBindings(new detail(), document.getElementById('detail'));

    nav.raise_errors = true;
    //start the app at today's date by setting the observable. 
    nav.run('#/today');



});