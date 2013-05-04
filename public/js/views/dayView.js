define([
    'knockout',
    'underscore',
    'vendor/require.text!templates/day/template.html',
    'jquery',
    'models/observables',
    'models/nodeModel',
    'moment'
], function(ko, _, template, $, obs, node, moment) {

    $('body').append(template);

    return function() {
        // this will update the class of the day form, so we know what to store. 
        // maybe make it a hidden field instead
        this.node = new node();
        
        //changing date means storing the old in the array, and loading the new. 
        obs.date.subscribe(function() {

            this.storeUpdatedEntry();

            //find the entry for the current day, or create a new one
            var find = _.find(obs.days(), function(val) {
                if (obs.date().format('YYYYMMDD') ===
                        moment(new Date(val.date).toString()).format('YYYYMMDD')) {

                    return true;
                }
            }.bind(this));
            
            this.node.unsubscribeAll();

            if (typeof find !== 'undefined') {
                this.fillNode(find);
            } else {
                this.fillNode({
                    'date': parseInt((obs.date().unix() * 1000).toString()),
                    'periodStart': false,
                    'periodEnd': false,
                    'opkSurge': false,
                    'comment': ''
                });
                this.storeUpdatedEntry();
            }
            
            this.node.subscribeAll();

        }.bind(this));

        this.storeUpdatedEntry = function() {
            if (typeof this.node.date() !== 'undefined') {
                var filtered = _.filter(obs.days(), function(val) {
                    return val.date !== this.node.date();
                }, this);
                var store = JSON.parse(ko.toJSON(this.node));
                //console.log('updating entry: ' + store);
                filtered.push(store);
                obs.days(filtered);
            }
            //console.log(obs.days());
        };

        this.fillNode = function(find) {
            this.node.date(find.date);
            this.node.periodStart(find.periodStart);
            this.node.periodEnd(find.periodEnd);
            this.node.opkSurge(find.opkSurge);
            this.node.comment(find.comment);
        };

    };

});