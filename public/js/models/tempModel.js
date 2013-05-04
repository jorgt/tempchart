define([
    'jquery',
    'models/observables'
], function($, obs) {

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
                        periodStart: value.period_start === 'true',
                        periodEnd: value.period_end === 'true',
                        opkSurge: value.opk_surge === 'true',
                        comment: value.comment || ''
                    });
                });

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

        }

    };

    return model;
});