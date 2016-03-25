import 'angular';
var $ = require('jquery');
var colorbrewer =  require('../../libs/colorbrewer').colorbrewer;

function ColorStripDirective(ColorScale) {
    return {
        restrict: 'E',
        replace:  false,
        scope:    {
            data:       '=',
            height:     '@',
            width:      '@',
            showLabels: '@',
            timeDomain: '@',
            api:        '='
        },
        link: (scope, element, attrs) => {

            scope.data.then((data) => {
                initChart(data, scope, element, attrs, ColorScale);
            });

            var el = $(element[0]);

            /**
             * Expose api object
             * @type {{dimByProcess: dimByProcessFn, dimByWindow: dimByWindowFn, undim: undimFn}}
             */
            scope.api = {
                dimByProcess: dimByProcessFn,
                dimByWindow:  dimByWindowFn,
                undim:        undimFn
            };

            /**
             * Dim all intervals expect one by process_id
             * @param {integer} except_id
             */
            function dimByProcessFn(except_id){
                d3.selectAll(el.find('rect.interval')).classed('interval-hide', true);
                d3.selectAll(el.find('rect.interval[process="' + except_id +'"]')).classed('interval-hide', false);
            }
            /**
             * Dim all intervals expect one by window_id
             * @param {integer} except_id
             */
            function dimByWindowFn(except_id){
                d3.selectAll(el.find('rect.interval')).classed('interval-hide', true);
                d3.selectAll(el.find('rect.interval[window="' + except_id +'"]')).classed('interval-hide', false);
            }

            /**
             * Undim all intervals
             */
            function undimFn(){
                d3.selectAll(el.find('rect.interval')).classed('interval-hide', false);
            }
        }
    };
}

angular
    .module('app')
    .directive('colorstrip', ['ColorScale', ColorStripDirective]);

function initChart(data, scope, element, attrs, colors){
    var values = data;
    var xDomain = scope.timeDomain || timeLineExtent(data);
    var margin = {left: 10, right: 10};
    var width = 1140 - margin.left - margin.right;
    var tickFormat = scope.tickFormat || d3.time.format.multi([
            [".%L", function(d) { return d.getMilliseconds(); }],
            [":%S", function(d) { return d.getSeconds(); }],
            ["%H:%M", function(d) { return d.getMinutes(); }],
            ["%H", function(d) { return d.getHours(); }],
            ["%a %d", function(d) { return d.getDay() && d.getDate() != 1; }],
            ["%b %d", function(d) { return d.getDate() != 1; }],
            ["%B", function(d) { return d.getMonth(); }],
            ["%Y", function() { return true; }]
        ]);
    var el = $(element[0]);

    var x = d3.time.scale()
        .domain(xDomain)
        .range([0, width]);
    var xAxis = d3.svg.axis().scale(x).tickFormat(tickFormat);

    var svg = d3.select(element[0]).append('svg')
        .attr('width', width + margin.left + margin.right)
        .attr('height', 95)
        .append('g')
        .attr('transform', 'translate('+margin.left + ',0)');

    svg.append('g')
        .attr('class', 'x-axis')
        .attr('transform', 'translate(0, 42)')
        .call(xAxis);

    /**
     * Draw elements
     * @param data
     */
    function draw(data) {
        // change domain
        x.domain(timeLineExtent(data));

        // change x axis
        svg.select('.x-axis')
            .call(xAxis);

        // select exist elements
        var elements = svg.selectAll('.interval')
            .data(data, function (d) { return d.id });

        // move exist elements
        elements.transition()
            .attr('cx', function (d) {return x(new Date(d.start))})
            .attr('width', function (d) { return x(new Date(d.end)) - x(new Date(d.start)) });

        // draw new elements
        elements.enter()
            .append('rect')
            .attr('class', 'interval')
            .attr('x', function (d) { return x(new Date(d.start)) })
            .attr('y', 0)
            .attr('width', function (d) { return x(new Date(d.end)) - x(new Date(d.start)) })
            .attr('height', '40px')
            .attr('process', function (d) { return d.process.id })
            .attr('window', function (d) { return d.window.id })
            .style('fill', function (d) { return colors(d.process.id) });

        // remove elements
        elements.exit()
            .remove();
    }

    function timeLineExtent(timelineData) {
        var extent1 = d3.extent(timelineData, function(a){return new Date(a.start)});
        var extent2 = d3.extent(timelineData, function(a){return new Date(a.end)});
        return [
            Math.min(extent1[0], extent2[0]),
            Math.max(extent1[1], extent2[1])
        ];
    }

    draw(values);

    // legend
    var legend = svg.append('g')
        .attr('class', 'legend')
        .attr('transform', 'translate(0, 85)');
    var process = {};
    values.forEach(function(v){
        process[v.process.id] = v.process.name;
    });
    var xOffset = 0;
    colors.domain().forEach(function(pid, index){
        var legendLine = legend.append('g')
            .attr('class', 'legend-item')
            .append('g')
            .attr('transform', 'translate('+(xOffset)+', 0)');
        legendLine.append('circle')
            .attr('cx', 0)
            .attr('cy',0)
            .attr('r', 6)
            .style('fill', colors(pid));
        legendLine.append('text')
            .text(process[pid] ? process[pid] : 'n/a')
            .attr('x', 8)
            .attr('y', 4);
        var box = legendLine[0][0].getBoundingClientRect();
        xOffset += box.width + 10;
    });
}
