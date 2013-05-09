define([
    'flotr',
    'models/observables',
    'models/tempModel',
    'moment'
], function(Flotr, obs, model, moment) {
    "use strict";

    return function(element) {

        this.element = document.getElementById(element);
        //this.model = new model();
        this.graph = null;
        this.xaxis;
        this.offset = 0;
        window.scrollingGraph = true;
        this.start;
        
        obs.initialized.subscribe(function() {
            this.initialize();
        }.bind(this));

        this.initialize = function() {
            var first = obs.days()[0].date;
            var last = obs.days()[obs.days().length - 1].date;
            this.options = {
                xaxis: {
                    min: first,
                    max: last,
                    //noTicks: 20,
                    mode: 'time',
                    timeFormat: '%d.%m',
                    tickFormatter: function(t) {
                        var d = moment(new Date(t).toString());
                        var label = '';

                        if (moment().format('YYYYMMDD') === d.format('YYYYMMDD')) {
                            label = 'label label-important';
                        } else if (d.format('YYYYMMDD') === obs.date().format('YYYYMMDD')) {
                            label = 'label label-info';
                        }

                        return '<a class="chart-day-link ' + label + '" '
                                + 'href="#/' + d.format('YYYY/MM/DD') + '">'
                                + d.format('DD.MM')
                                + '</a>';

                    },
                    timeMode: 'local'
                },
                yaxis: {
                    min: 35.5,
                    max: 37,
                    tickSize: 0.05,
                    noTicks: 40,
                    showMinorLabels: true,
                    tickFormatter: function(click) {
                        var s = click.toString();
                        return (s.substring(3) === '000' || s.substring(3) === '500') ? s.substring(0, 5) : s.substring(5, 2);
                    }
                },
                mouse: {
                    track: true,
                    trackDecimals: 2,
                    trackFormatter: function(e) {
                        return  'Day: '
                                + moment(new Date(parseInt(e.x)).toString()).format('dddd, MMMM Do YYYY')
                                + '<br>'
                                + 'Temperature: '
                                + e.y;
                    },
                    radius: 1,
                    sensibility: 50
                },
                points: {show: true},
                lines: {show: true, fill: true, fillOpacity: 0.1}
            };

            this.draw();
        }.bind(this);

        this.draw = function() {
            this.graph = Flotr.draw(this.element, [obs.data()], this.options);
        }.bind(this);

        //this.initialize();
    };

});