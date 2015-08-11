$(function(){
    var values = dashboardTimeline;

    var margin = {left: 10, right: 10};
    var width = 1140 - margin.left - margin.right;

    var xDomain = d3.extent(values, function(value){
        return new Date(value.start);
    });

    var colors = processColor;

    var x = d3.time.scale()
        .domain(xDomain)
        .range([0, width]);
    var xAxis = d3.svg.axis().scale(x);

    var svg = d3.select('#color-strip').append('svg')
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
});

function colorStripDim(except_id) {
    d3.selectAll('#color-strip rect.interval').classed('interval-hide', true);
    d3.selectAll('#color-strip rect.interval[process="' + except_id +'"]').classed('interval-hide', false);
}
function colorStripDimByWindow(except_id) {
    d3.selectAll('#color-strip rect.interval').classed('interval-hide', true);
    d3.selectAll('#color-strip rect.interval[window="' + except_id +'"]').classed('interval-hide', false);
}

function colorStripUndim(){
    d3.selectAll('#color-strip rect.interval').classed('interval-hide', false);
}