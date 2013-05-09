define([
    'jquery',
    'knockout',
    'models/tempModel'
], function($, ko, model) {

    return function() {
        this.date = ko.observable();
        this.period = ko.observable();
        this.spotting = ko.observable();
        this.opkSurge = ko.observable();
        this.comment = ko.observable();

        this.model = new model();

        this.subscribers = [];

        this.subscribeAll = function() {

            this.subscribers.period = this.period.subscribe(function(val) {
                this.model.put({'date': this.date(), 'period': val});

            }.bind(this));

            this.subscribers.spotting = this.spotting.subscribe(function(val) {
                this.model.put({'date': this.date(), 'spotting': val});
            }.bind(this));

            this.subscribers.opkSurge = this.opkSurge.subscribe(function(val) {
                this.model.put({'date': this.date(), 'opk_surge': val});
            }.bind(this));

            this.subscribers.comment = this.comment.subscribe(function(val) {
                this.model.put({'date': this.date(), 'comment': val});
            }.bind(this));
        };

        this.unsubscribeAll = function() {
            for (var i in this.subscribers) {
                this.subscribers[i].dispose();
            }
        };
    };

});