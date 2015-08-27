$(function(){
    var values = dashboardKeys;

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

    var xAxis = d3.svg.axis().scale(x).orient('bottom');
    var yAxis = d3.svg.axis()
        .scale(y)
        .orient("right")
        .ticks(10);

    var svg = d3.select('#keys-activity').append('svg')
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

    svg.selectAll()
        .data(values)
        .enter()
        .append('rect')
        .attr('class', 'bar')
        .attr('x', function (d) {return x(new Date(d.date))})
        .attr('width', 2)
        .attr('y', function (d) {return y(d.count)})
        .attr('height', function (d) {return height - y(d.count)})
});
