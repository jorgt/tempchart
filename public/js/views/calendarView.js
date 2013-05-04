define([
    'knockout',
    'jquery',
    'vendor/require.text!templates/calendar/field.html',
    'vendor/require.text!templates/calendar/row.html',
    'vendor/require.text!templates/calendar/template.html',
    'moment',
    'models/observables'
], function(ko, $, field, row, template, moment, obs) {
    
    

    $('body').append(template);
    $('body').append(field);
    $('body').append(row);

    return function() {

        this.rows = ko.observableArray();

        this.setNewDate = function(d) {
            location.hash = '#/' + d.format('YYYY/MM/DD');
        }.bind(this);

        this.prevMonth = function(e) {
            this.setNewDate(moment(obs.date()).subtract('month', 1));
            return false;
        }.bind(this);

        this.nextMonth = function(e) {
            this.setNewDate(moment(obs.date()).add('month', 1));
            return false;
        }.bind(this);

        this.viewDay = function(e) {
            if (e.class === 'muted') {
                return false;
            } else {
                this.setNewDate(moment(obs.date()).date(e.day));
            }
        }.bind(this);

        this.createCalendar = function(incDate) {
            this.rows([]);

            var first = moment(incDate).startOf('month');
            var prev = moment(incDate)
                    .subtract('month', 1)
                    .endOf('month')
                    .subtract('days', first.day()-1)
                    .date();

            var ar = [];
            var ld = 1;
            for (wd = 1; wd < (moment(first).endOf("month").daysInMonth() + moment(first).endOf("month").day() + first.day() + 7); wd++) {
                if (first.day() < wd && wd <= moment(first).endOf("month").daysInMonth() + first.day()) {
                    
                    var cd = moment(first.year() + '/' + (first.month() + 1) + '/' + (wd - first.day()));
                    var css = '';
                    if (cd.format('YYYYMMDD') === moment(incDate).format('YYYYMMDD')) {
                        css = 'active';
                    }
                    
                    if (cd.format('YYYYMMDD') === moment().format('YYYYMMDD')) {
                        css = 'today';
                    }
                    
                    ar.push({'day': wd - first.day(), 'class': css});
                } else if (wd < 7) {
                    ar.push({'day': prev++, 'class': 'muted'});
                } else if (ar.length < 7 && wd < moment(first).endOf("month").date() + first.day() + 7) {
                    ar.push({'day': ld++, 'class': 'muted'});
                }

                if (ar.length === 7) {
                    this.rows.push(ar);
                    ar = [];
                }
            }
        }.bind(this);

        //finish off by adding subscribers for the calendar to the global date observable.
        obs.date.subscribe(this.createCalendar);
    };

});