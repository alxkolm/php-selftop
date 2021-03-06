var ViewIndex = require('../views/dashboard/index');
var ViewFilter = require('../views/dashboard/filter-date');
var ViewTask = require('../views/dashboard/tasks');
var Backbone = require('backbone');
var $ = require('jquery');

module.exports = function (options) {
    var app = options.app;
    return {
        index: function () {
            console.log('route: dashboard/index');
            var view = new ViewIndex;
            app.mainView.renderPage(view);
            view.initCharts();


            // add filter to toolbar
            var viewFilter = new ViewFilter({
                className: 'five wide column right floated'
            });
            app.toolbar.append(viewFilter.render().el);

            // add task list
            //var viewTask = new ViewTask({
            //    collection: app.tasks
            //});
            //view.$el.append(viewTask.render().el);
        }
    };
};