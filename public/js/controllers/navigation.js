define([
    'models/observables',
    'sammy', //why is sammy slower than jquery!
    'moment',
    'models/tempModel'
], function(obs, Sammy, moment, model) {


    //TODO the draw function in the graph seems stuck... keeps getting back to 'today'. 
    return Sammy(function() {

        this.get('#/loading/(.*)', function() {
            var m = new model();
            m.get(function() {
                obs.initialized(true);
                this.reroute(location.hash.substring(10));
            }.bind(this));
        }.bind(this));

        this.get('#/([0-9]{4})\/([0-9]{1,2})\/([0-9]{1,2})', function() {
            
            if (obs.initialized() === false) {
                this.reroute(location.hash.substring(2));
                
            } else {
                setTimeout(1000, detail());
                obs.date(moment(location.hash.substring(2)));
            }
        }.bind(this));

        this.get('#/today', function() {
            obs.layout('today');
            this.reroute(moment().format('YYYY/MM/DD'));
        }.bind(this));

        this.get('#/last', function() {
            obs.layout('last');
            if (obs.days().length > 0) {
                this.reroute(moment(new Date(obs.days()[obs.days().length - 1].date)).format('YYYY/MM/DD'));
            } else {
                this.reroute('today');
            }
        }.bind(this));

        this.get('#/first', function() {
            obs.layout('first');
            if (obs.days().length > 0) {
                this.reroute(moment(new Date(obs.days()[0].date)).format('YYYY/MM/DD'));
            } else {
                this.reroute('today');
            }
        }.bind(this));

        this.get('#/overview', function() {
            obs.layout('overview');
            this.reroute('overview');

            overview();

        }.bind(this));

        this.reroute = function(loc) {
            if (obs.initialized() === false) {
                location.hash = '#/loading/' + loc;
            } else {
                location.hash = '#/' + loc;
            }
        }.bind(this);

        function overview() {
            $('#detail').hide();
            $('#overview').fadeIn();
        }

        function detail() {
            $('#overview').hide();
            $('#detail').fadeIn();
        }
    });

});