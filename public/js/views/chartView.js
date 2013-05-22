define([
    'flotr',
    'models/observables',
    'models/tempModel',
    'moment'
], function(Flotr, obs, model, moment) {
    "use strict";

    /**
     *
     */
    return function(element) {

        this.element = document.getElementById(element);
        this.model = new model();
        this.graph = null;
        this.xaxis;
        this.offset = 0;
        window.scrollingGraph = true;
        this.start;

        /**
         *
         * @type {*}
         */
        this.initialize = function() {

            this.options = {
                xaxis: {
                    min: moment(obs.date().format('YYYY/MM/DD')).subtract('days', 7).unix() * 1000,
                    max: moment(obs.date().format('YYYY/MM/DD')).add('days', 7).unix() * 1000,
                    noTicks: 20,
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


        /**
         *
         * @type {*}
         */
        this.removePoint = function(e) {
            var pos = this.graph.getEventPosition(e);
            if (pos.x !== undefined && pos.y !== undefined && window.scrollingGraph === true) {
                var x = pos.x + this.offset;
                var y = pos.y;
                var dx = this.roundDateToDay(x);
                y = Math.round(y * 20) / 20;
                obs.data(_.filter(obs.data(), function(entry) {
                    if (moment(new Date(entry[0]).toString()).format('YYYYMMDD') === dx.format('YYYYMMDD')) {
                        this.model.del({date:dx.unix()*1000});
                    } else {
                        return entry;
                    }
                }.bind(this)));
                //todo this filter remove the whole line. fixit. 
                this.draw();
            }
        }.bind(this);

        /**
         *
         * @type {*}
         */
        this.initializeDrag = function(e) {
            this.start = this.graph.getEventPosition(e);
            Flotr.EventAdapter.observe(this.element, 'mousemove', this.move);
            Flotr.EventAdapter.observe(this.element, 'click', this.addPoint);
            Flotr.EventAdapter.observe(document, 'mouseup', this.onMouseUp);
        }.bind(this);

        /**
         *
         * @type {*}
         */
        this.onMouseUp = function(e) {
            setTimeout(function() {
                window.scrollingGraph = true; //setting enabled to true AFTER the click event fires
            }, 50);
            Flotr.EventAdapter.stopObserving(this.element, 'mousemove', this.move);
            location.hash = '#/' + moment(new Date(this.graph.axes.x.ticks[7].v)).format('YYYY/MM/DD');
        }.bind(this);

        /**
         *
         * @type {*}
         */
        this.move = function(e) {
            window.scrollingGraph = false;
            this.skip = true;
            var end = this.graph.getEventPosition(e);
            this.xaxis = this.graph.axes.x;
            this.offset = this.start.x - end.x;
            var newx = {'min': this.xaxis.min + this.offset, 'max': this.xaxis.max + this.offset};
            this.draw(newx);
            Flotr.EventAdapter.observe(this.graph.overlay, 'mousedown', this.initializeDrag);
        }.bind(this);

        /**
         *
         * @type {*}
         */
        this.draw = function(x) {
            var newx = x || [];
            newx.min = newx.min || this.options.xaxis.min;
            newx.max = newx.max || this.options.xaxis.max;

            // Clone the options, so the 'options' variable always keeps intact.
            var opt = Flotr._.extend(Flotr._.clone(this.options));
            opt.xaxis.min = newx.min;
            opt.xaxis.max = newx.max;

            this.graph = Flotr.draw(this.element, [obs.data()], opt);
        }.bind(this);

        /**
         *
         * @type {*}
         */
        this.addPoint = function(e) {
            var pos = this.graph.getEventPosition(e);
            if (pos.x !== undefined && pos.y !== undefined && pos.y >= 35.50 && window.scrollingGraph === true) {

                var x = this.roundDateToDay(pos.x + this.offset);
                var y = Math.round(pos.y * 20) / 20;

                this.addToDateArray(x, y);
                // Sort the series.
                obs.data.sort(function(a, b) {
                    return a[0] - b[0];
                });

                this.draw();
            }
        }.bind(this);

        /**
         *
         * @param m
         * @returns {*}
         */
        this.roundDateToDay = function(m) {
            if (moment(m).hour() < 12) {
                return moment(moment(m).format('YYYY/MM/DD'));
            } else {
                return moment(moment(m).format('YYYY/MM/DD')).add('day', 1);
            }
        };

        /**
         *
         * @type {*}
         */
        this.addToDateArray = function(date, temp) {
            var ar = obs.data();
            var found = _.some(ar, function(entry) {
                return date.format('YYYYMMDD') === moment(new Date(entry[0]).toString()).format('YYYYMMDD');
            });
            if (found === false) {
                //console.log('creating entry: ' + date + ' ' + temp);
                this.model.post({'date': date.unix() * 1000, 'temperature': temp});
                obs.data.push([date.unix() * 1000, temp]);
            }

        }.bind(this);

        this.initialize();
        obs.date.subscribe(this.initialize);
        Flotr.EventAdapter.observe(this.element, 'dblclick', this.removePoint);
        Flotr.EventAdapter.observe(this.graph.overlay, 'mousedown', this.initializeDrag);
    };

});