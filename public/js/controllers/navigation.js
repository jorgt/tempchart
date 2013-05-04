define([
    'models/observables',
    'sammy', //why is sammy slower than jquery!
    'moment',
    'models/tempModel'
], function(obs, Sammy, moment, model) {

    //TODO the draw function in the graph seems stuck... keeps getting back to 'today'. 
    return Sammy(function() {
        this.initialized = false;

        this.get('#/loading/(.*)', function() {
            var m = new model();
            m.get(function() {
                this.initialized = true;
                this.reroute(location.hash.substring(10));
            }.bind(this));
        }.bind(this));

        this.get('#/([0-9]{4})\/([0-9]{1,2})\/([0-9]{1,2})', function() {
            if (this.initialized === false) {
                this.reroute(location.hash.substring(2));
            } else {
                obs.date(moment(location.hash.substring(2)));
            }
        }.bind(this));

        this.get('#/today', function() {
            this.reroute(moment().format('YYYY/MM/DD'));
        }.bind(this));

        this.get('#/last', function() {
            this.reroute('today');
        }.bind(this));

        this.get('#/first', function() {
            this.reroute('today');
        }.bind(this));

        this.get('#/overview', function() {
            this.reroute('today');
        }.bind(this));

        this.reroute = function(loc) {
            if (this.initialized === false) {
                location.hash = '#/loading/' + loc;
            } else {
                location.hash = '#/' + loc;
            }
        }.bind(this);

    });

});