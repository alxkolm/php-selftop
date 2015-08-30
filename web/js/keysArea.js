$(function(){
    var values = [];
    var total = 0;
    dashboardKeys.forEach(function(item){
        total += parseInt(item.count);
        values.push({date: new Date(item.date), count: total})
    });

    var margin = {left: 10, right: 10};
    var width = 1140 - margin.left - margin.right;
    var height = 70;

    var xDomain = dashboard.timeExtent;

    var yDomain = d3.extent(values, function(value){
        return value.count;
    });

    var x = d3.time.scale()
        .domain(xDomain)
        .range([0, width]);

    var y = d3.scale.linear()
        .domain(yDomain)
        .range([height, 0]);

    var area = d3.svg.area()
        .x(function(d) { return x(d.date); })
        .y0(height)
        .y1(function(d) { return y(d.count); });

    var xAxis = d3.svg.axis().scale(x).orient('bottom');
    var yAxis = d3.svg.axis()
        .scale(y)
        .orient("right")
        .ticks(10);

    var svg = d3.select('#keys-activity-area').append('svg')
        .attr('width', width + margin.left + margin.right)
        .attr('height', height+50)
        .append('g')
        .attr('transform', 'translate('+margin.left + ',0)');

    svg.append("g")
        .attr("class", "x-axis")
        .attr("transform", "translate(0," + height + ")")
        .call(xAxis);

    svg.append("g")
        .attr("class", "y-axis")
        .call(yAxis);

    svg.append("path")
        .datum(values)
        .attr("class", "area")
        .style('fill', '#1F77B4')
        .attr("d", area);
});
