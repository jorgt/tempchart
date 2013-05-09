define([
    'jquery',
    'underscore',
    'models/observables'
], function($, _, obs) {

    var model = function() {
        this.notesURL = '/tempchart/api/temp/notes/';
    };

    model.prototype = {
        ajax: function(data, method, success) {
            $.ajax({
                type: method,
                url: this.notesURL,
                data: data,
                error: function(json) {
                    console.log('error loading json');
                    console.log(json);
                },
                success: success
            });
        },
        get: function(callback) { //get ALL
            this.ajax('', 'GET', function(json) {

                $.each(json.notes, function(key, value) {
                    obs.data.push([
                        parseInt(value.date),
                        parseFloat(value.temperature)
                    ]);

                    obs.days.push({
                        date: parseInt(value.date),
                        period: value.period === 'true',
                        spotting: value.spotting === 'true',
                        opkSurge: value.opk_surge === 'true',
                        comment: value.comment || ''
                    });
                });

                obs.days(_.sortBy(obs.days(), function(val) {
                    return val.date;
                }));

                callback();

            });
        },
        post: function(data) { //create
            this.ajax(
                    data,
                    'POST',
                    function(json) {
                        //console.log(json);
                    });
        },
        put: function(data) { //update
            this.ajax(
                    data,
                    'PUT',
                    function(json) {
                        //console.log(json);
                    });
        },
        del: function(data) { //delete
            this.ajax(
                    data,
                    'DELETE',
                    function(json) {
                        //console.log(json);
                    });
        }

    };

    return model;
});