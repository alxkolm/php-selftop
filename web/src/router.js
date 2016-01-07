var Backbone = require('backbone');
var BackboneRouteControl = require('backbone-route-control');

module.exports = BackboneRouteControl.extend({
    routes: {
        //'/': 'dashboard#index',
        '': 'dashboard#index'
        //'default': 'dashboard#index',
        //'whatever': 'dashboard#index'
    }
});