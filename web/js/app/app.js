var Router = require('./router');
var Backbone = require('backbone');
var DashboardController = require('./controllers/dashboard');
var MainView = require('./views/main-view');

module.exports = function (options) {
    this.router = new Router({
        app: this,
        controllers: {
            dashboard: new DashboardController({app: this})
        }
    });

    this.mainView = new MainView({
        el: $('#app')
    });

    this.toolbar = $('#toolbar-main');

    this.showApp = function () {
        Backbone.history.start({ pushState: true });
    };

    this.showApp();
};