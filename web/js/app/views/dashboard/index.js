var Backbone = require('backbone');
var Template = require('./templates/index.html');
var ProcessModalTemplate = require('./templates/process-modal.html');
var _        = require('underscore');
//var $        = require('jquery');
require('../../components/sunburst');
require('../../components/color-strip');
require('../../css/dashboard.css');
//debugger;

module.exports = Backbone.View.extend({
    render: function () {
        this.$el.html(_.template(Template)());

        // Init charts
        this.initChartProcessStrip();
        this.initChartSunburstWindows();
        this.initChartSunburstTasks();
        this.initChartSunburstClusters();

        return this;
    },
    initChartProcessStrip: function () {
        $('#process-strip', this.$el).colorStrip({
            data:       dashboardTimeline,
            color:      dashboard.processColor,
            xDomain:    dashboard.timeExtent,
            tickFormat: dashboard.tickFormat
        });
    },
    initChartSunburstWindows: function () {
        var stripChart = $('#process-strip', this.$el)[0];
        $('#sunburst-windows', this.$el).sunburst({
            color: dashboard.processColor,
            data: dashboardDurations,
            mouseleave: stripChart.undim,
            mouseover:  (d, el) => {
                if (d.depth == 1) {
                    stripChart.dim(d.process_id);
                } else if (d.depth == 2) {
                    stripChart.dimByWindow(d.window_id);
                }
            },
            onclick: (d, el) => {
                if (d.depth == 1) {
                    this.showProcessPopup(d);
                }
            },
            dragend: function (d) {
                var el = $(d3.event.sourceEvent.toElement);
                var taskId = el.attr('task-id');
                var window_id;
                var process_id;
                switch (d.depth) {
                    case 1:
                        process_id = d.process_id;
                        break;
                    case 2:
                        window_id = [d.window_id];
                        break;
                }

                $.ajax('/record/assign', {
                    type: 'POST',
                    data: {task: taskId, window: window_id, process: process_id},
                    success: function(){
                        el.css({backgroundColor: 'green'}).animate({backgroundColor: 'none'});
                    }
                });
            }
        });
    },
    initChartSunburstClusters: function () {
        if (typeof dashboardClustersDurations != 'undefined'){
            $('#sunburst-clusters', this.$el).sunburst({
                color: dashboard.clusterColor,
                data: dashboardClustersDurations,
                onclick: (d, el) => {
                    if (d.depth == 1) {
                        this.showProcessPopup(d);
                    }
                },
                dragend: function (d) {
                    var el = $(d3.event.sourceEvent.toElement);
                    var taskId = el.attr('task-id');
                    var window_id;
                    switch (d.depth) {
                        case 1:
                            window_id = d.children.map(function (a) {
                                return a.window_id;
                            });
                            break;
                        case 2:
                            window_id = [d.window_id];
                            break;
                    }

                    $.ajax('/record/assign', {
                        type: 'POST',
                        data: {task: taskId, window: window_id},
                        success: function(){
                            el.css({backgroundColor: 'green'}).animate({backgroundColor: 'none'});
                        }
                    });
                }
            });
        }
    },
    initChartSunburstTasks: function () {
        $('#sunburst-task', this.$el).sunburst({
            color: dashboard.taskColor,
            data: dashboardTaskDurations
        });
    },
    showProcessPopup: function (data) {
        console.log(data);
        var el = $(_.template(ProcessModalTemplate)({data: data}));
        $(el).modal('show');
    }
});