define([
    'models/observables',
    'knockout',
    'moment'
], function(obs, ko,moment) {
    //"use strict";

    return function() {
        this.dateString = ko.observable();
        this.active = ko.observable();
        
        obs.date.subscribe(function(d) {
            this.dateString(moment(d).format('dddd, Do of MMMM, YYYY'));
        }.bind(this));
        
        obs.layout.subscribe(function(v) {
           if(v === 'overview') {
               this.dateString('Overview'); 
           }
           this.active(v);
        }.bind(this));

        //get and set details from DB on date change. 
    };
});