(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD. Register as an anonymous module.
        define(['jquery'], factory);
    } else if (typeof module === 'object' && module.exports) {
        // Node/CommonJS
        module.exports = factory(require('jquery'));
    } else {
        // Browser globals
        factory(jQuery);
    }
}(function ($) {
    require('../css/color-strip.css');
    require('../css/axis.css');
    $.fn.colorStrip = function (options) {
        this.addClass('color-strip');
        var values = options.data;

        var margin = {left: 10, right: 10};
        var width = 1140 - margin.left - margin.right;

        var xDomain = options.xDomain;

        var colors = options.color;

        var x = d3.time.scale()
            .domain(xDomain)
            .range([0, width]);
        var xAxis = d3.svg.axis().scale(x).tickFormat(options.tickFormat);

        var svg = d3.select(this[0]).append('svg')
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

        var update = function (data) {
            console.log('update color strip ...');
            draw(data);
        };

        /**
         * Hide elements
         * @param except_id process_id that skip to hide
         */
        var colorStripDim = function (except_id) {
            d3.selectAll($(this).find('rect.interval')).classed('interval-hide', true);
            d3.selectAll($(this).find('rect.interval[process="' + except_id +'"]')).classed('interval-hide', false);
        };

        /**
         * Hide elements
         * @param except_id window_id that skip to hide
         */
        var colorStripDimByWindow = function (except_id) {
            d3.selectAll($(this).find('rect.interval')).classed('interval-hide', true);
            d3.selectAll($(this).find('rect.interval[window="' + except_id +'"]')).classed('interval-hide', false);
        };

        /**
         * Show all elements
         */
        var colorStripUndim = function (){
            d3.selectAll($(this).find('rect.interval')).classed('interval-hide', false);
        };



        // Attach functions
        this[0].dim         = $.proxy(colorStripDim, this);
        this[0].dimByWindow = $.proxy(colorStripDimByWindow, this);
        this[0].undim       = $.proxy(colorStripUndim, this);
        this[0].update      = $.proxy(update, this);
    };
}));
