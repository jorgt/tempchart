define(['knockout', 'moment'], function(ko, moment) {
    var observable = [];
    
    observable.date = ko.observable(moment());
    observable.data = ko.observableArray();
    observable.days = ko.observableArray();
    observable.layout = ko.observable();
    observable.initialized = ko.observable(false);
    
    return observable;
});

