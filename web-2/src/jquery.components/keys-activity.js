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
    require('./css/axis.css');
    $.fn.keys = function (options) {
        var values = [];
        options.data.forEach(function(item){
            values.push({date: new Date(item.date), count: item.count})
        });

        var margin = {left: 10, right: 10};
        var width = 1140 - margin.left - margin.right;
        var height = 70;

        var xDomain = options.xDomain;

        var yDomain = d3.extent(values, function(value){
            return value.count;
        });

        var x = d3.time.scale().clamp(true)
            .domain(xDomain)
            .range([0, width]);

        var y = d3.scale.linear()
            .domain(yDomain)
            .range([height, 0]);

        var area = d3.svg.area()
            .x(function(d) { return x(d.date); })
            .y0(height)
            .y1(function(d) { return y(d.count); });

        var xAxis = d3.svg.axis().scale(x).orient('bottom').tickFormat(options.tickFormat);
        var yAxis = d3.svg.axis()
            .scale(y)
            .orient("right")
            .ticks(10);

        var svg = d3.select(this[0]).append('svg')
            .attr('width', width + margin.left + margin.right)
            .attr('height', height+50)
            .append('g')
            .attr('transform', 'translate('+margin.left + ',0)');

        var xAxisEl = svg.append("g")
            .attr("class", "x-axis")
            .attr("transform", "translate(0," + height + ")");
        xAxisEl.call(xAxis);

        var yAxisEl = svg.append("g")
            .attr("class", "y-axis");
        yAxisEl.call(yAxis);



        svg.append('text')
            .attr('y', 10)
            .attr('x', 80)
            .text('Key press per minute');

        var path = svg.append("path");
        path
            .datum(values)
            .attr("class", "area")
            .style('fill', '#dc322f')
            .attr("d", area);

        function draw(values, xDomain){
            yDomain = d3.extent(values, function(value){
                return value.count;
            });
            //
            y.domain(yDomain);
            x.domain(xDomain);

            xAxisEl.call(xAxis);
            yAxisEl.call(yAxis);

            // TODO: rebuild line
            path.datum(values)
                .attr("d", area);
        }

        draw(values, xDomain);

        function update(data, timeDomain){
            console.log('update keys');
            var values = [];
            data.forEach(function(item){
                values.push({date: new Date(item.date), count: item.count})
            });
            draw(values, timeDomain)
        }

        this[0].update = $.proxy(update, this);
    };
}));
