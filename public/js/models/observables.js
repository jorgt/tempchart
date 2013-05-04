define(['knockout', 'moment'], function(ko, moment) {
    var observable = [];
    
    observable.date = ko.observable(moment());
    observable.data = ko.observableArray();
    observable.days = ko.observableArray();
    
    return observable;
});

