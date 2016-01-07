var App = require('./app');
var $ = require('jquery');

$(function () {
    window.app = new App();
    window.app.showApp();
});
