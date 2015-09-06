var TasksView = require('./views/dashboard/tasks');
var Backbone = require('backbone');
var $ = require('jquery');
require('./css/dashboard.css');



$(function(){
    var tasksView = new TasksView({
        el: $('#tasks'),
        collection: new Backbone.Collection(dashboardTasks)
    });

    tasksView.render();
});