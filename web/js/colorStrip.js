$(function(){
    var values = dashboardTimeline;

    var margin = {left: 10, right: 10};
    var width = 1140 - margin.left - margin.right;

    var xDomain = d3.extent(values, function(value){
        return new Date(value.start);
    });

    var colors = d3.scale.category10();

    var x = d3.time.scale()
        .domain(xDomain)
        .range([0, width]);
    var xAxis = d3.svg.axis().scale(x);

    var svg = d3.select('#color-strip').append('svg')
        .attr('width', width + margin.left + margin.right)
        .attr('height', 70)
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
        .style('fill', function (d) {return colors(d.process.id)})
        .attr('data-legend', function (d) {return d.process.name});
    svg.append('g')
        .attr('class', 'x-axis')
        .attr('transform', 'translate(0, 42)')
        .call(xAxis);

    console.log(colors.domain(), colors.range());
});
