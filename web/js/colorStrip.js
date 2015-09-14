$.fn.extend({
    colorStrip: function (options) {
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

        var elements = svg.selectAll('.interval').data(values)
            .enter()
            .append('rect')
            .attr('class', 'interval')
            .attr('x', function (d) {return x(new Date(d.start))})
            .attr('y', 0)
            .attr('width', function (d) {return x(new Date(d.end)) - x(new Date(d.start))})
            .attr('height', '40px')
            .attr('process', function (d) {return d.process.id})
            .attr('window', function (d) {return d.window.id})
            .style('fill', function (d) {return colors(d.process.id)});
        svg.append('g')
            .attr('class', 'x-axis')
            .attr('transform', 'translate(0, 42)')
            .call(xAxis);

        // legend
        var legend = svg.append('g').attr('class', 'legend');
        var process = {};
        values.forEach(function(v){
            process[v.process.id] = v.process.name;
        });
        var xOffset = 0;
        colors.domain().forEach(function(pid, index){
            var legendLine = legend.append('g')
                .attr('transform', 'translate(0, 85)')
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
    }
});

$(function(){
    $('#color-strip').colorStrip({
        data:       dashboardTimeline,
        color:      dashboard.processColor,
        xDomain:    dashboard.timeExtent,
        tickFormat: dashboard.tickFormat
    });
});

