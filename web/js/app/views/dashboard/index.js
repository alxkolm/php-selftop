var Backbone = require('backbone');
var Template = require('./templates/index.html');
var HintTemplate = require('./templates/process-hint.html');
var ProcessModalTemplate = require('./templates/process-modal.html');
var _        = require('underscore');
require('../../components/sunburst');
require('../../components/color-strip');
require('../../components/keys-activity');
require('../../css/dashboard.css');
//debugger;

module.exports = Backbone.View.extend({
    render: function () {
        this.$el.html(_.template(Template)());
        return this;
    },
    initCharts: function () {
        this.initChartProcessStrip();
        this.initChartSunburstWindows();
        //this.initChartSunburstTasks();
        this.initChartSunburstClusters();
        this.initChartKeysActivity();
    },
    initChartProcessStrip: function () {
        var el = $('#process-strip', this.$el);
        el.colorStrip({
            data:       dashboardTimeline,
            color:      dashboard.processColor,
            xDomain:    dashboard.timeExtent,
            tickFormat: dashboard.tickFormat
        });

        app.on('update:timeline', el[0].update);
    },
    initChartSunburstWindows: function () {
        var stripChart = $('#process-strip', this.$el)[0];
        var el = $('#sunburst-windows', this.$el);
        el.sunburst({
            color: dashboard.processColor,
            data: dashboardDurations,
            showLabels: true,
            mouseleave: function (d, el) {
                stripChart.undim();
                var container = $(el).parents('.sunburst');
                container.popup('destroy');
            },
            mouseover:  (d, el) => {
                if (d.depth == 1) {
                    stripChart.dim(d.process_id);

                    var tpl = _.template(HintTemplate)({items: d.children.slice(0,5)});
                    var content = $(tpl);
                    var container = $(el).parents('.sunburst');
                    container.popup({
                        title: d.name,
                        position: 'right center',
                        variation: 'very wide',
                        html: content
                    });
                    container.popup('show');

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
                if (taskId == undefined){
                    return;
                }
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
        app.on('update:sunburst-windows', el[0].update);
    },
    initChartSunburstClusters: function () {
        if (typeof dashboardClustersDurations != 'undefined'){
            var chart = $('#sunburst-clusters', this.$el);
            chart.sunburst({
                color: dashboard.clusterColor,
                data: dashboardClustersDurations,
                onclick: (d, el) => {
                    if (d.depth == 1) {
                        this.showProcessPopup(d);
                    }
                },
                mouseover:  (d, el) => {
                    if (d.depth == 1) {
                        var tpl       = _.template(HintTemplate)({items: d.children.slice(0, 5)});
                        var content   = $(tpl);
                        var container = $(el).parents('.sunburst');
                        container.popup({
                            title:     d.name,
                            position:  'right center',
                            variation: 'very wide',
                            html:      content
                        });
                        container.popup('show');
                    }
                },
                mouseleave: function (d, el) {
                    var container = $(el).parents('.sunburst');
                    container.popup('destroy');
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
            app.on('update:sunburst-cluster', chart[0].update);
        }
    },
    initChartSunburstTasks: function () {
        var chart = $('#sunburst-task', this.$el);
        chart.sunburst({
            color: dashboard.taskColor,
            data: dashboardTaskDurations,
            dragend: function (d) {
                debugger;
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
        app.on('update:sunburst-task', chart[0].update);
    },
    initChartKeysActivity: function () {
        var chart = $('#keys-activity', this.$el);
        chart.keys({
            data: dashboardKeys,
            xDomain:    dashboard.timeExtent,
            tickFormat: dashboard.tickFormat
        });

        app.on('update:keys', chart[0].update);
    },
    showProcessPopup: function (data) {
        var el = $(_.template(ProcessModalTemplate)({data: data}));
        $(el).modal('show');
    }
});