var Router = require('./router');
var Backbone = require('backbone');
var DashboardController = require('./controllers/dashboard');
var MainView = require('./views/main-view');
var _ = require('underscore');
var $ = require('jquery');

module.exports = function (options) {
    _.extend(this, Backbone.Events);

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
    var app = this;

    this.loadData = (data) =>  {
        $.ajax('/app/data', {
            data:     data,
            dataType: 'json',
            type:     'POST',
            success:  (reply) => {
                app.trigger('update:timeline', reply.timeLine);
                app.trigger('update:sunburst-windows', reply.durationProcess);
                app.trigger('update:keys', reply.keys);
            }
        });
    };



    this.on('filter:date:change', this.loadData);

    return this;
};