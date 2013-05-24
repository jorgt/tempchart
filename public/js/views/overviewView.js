define([
    'flotr',
    'models/observables',
    'moment'
], function(Flotr, obs, moment) {
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
                    timeFormat: '%d.%m.%y',
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
                    sensibility: 350
                },
                points: {show: true},
                lines: {show: true, fill: true, fillOpacity: 0.1}
            };

            this.draw();
        }.bind(this);

        this.draw = function() {
            this.graph = Flotr.draw(this.element, [obs.data()], this.options);
            this.canvasHeight = this.graph.plotHeight;
            this.canvasWidth = this.graph.plotWidth;
            this.points = obs.days().length - 1;
            this.rectangleWidth = this.canvasWidth / this.points;
            this.offset = this.graph.canvasWidth - this.canvasWidth;

            console.log(this.graph);
            for (var i = 0; i < obs.days().length; i++) {
                if (obs.days()[i].period === true) {
                    this.rectangle('orange', i);
                }
                if (obs.days()[i].opkSurge === true) {
                    this.rectangle('green', i);
                }
            }

        }.bind(this);

        this.rectangle = function(color, row) {
            var context = this.graph.octx;


            context.beginPath();
            context.globalAlpha = 0.1;

            context.rect(this.offset + this.rectangleWidth * row,
                    0,
                    this.rectangleWidth,
                    this.canvasHeight);

            context.fillStyle = color;
            context.fill();
            context.globalAlpha = 1;
        }

        //this.initialize();
    };

});