$(function(){
    var x = d3.scale.linear()
        .domain([
            0,
            d3.max(window.dashboardProcessInfo, function(a){return a.keys})
        ])
        .range([0, 300]);
    var y = d3.scale.linear()
        .domain([
            0,
            d3.max(window.dashboardProcessInfo, function(a){return a.motions_filtered})
        ])
        .range([0, 300]);

    var svg = d3.select('#graph-process').append('svg')
        .attr('width', 297*2)
        .attr('height', 210*2);

    svg.select('#graph-process').selectAll('circle')
        .data(window.dashboardProcessInfo)
        .enter()
        .append('circle')
        .attr('cx', function (d) {return  x(d.keys)})
        .attr('cy', function (d) {return y(d.motions_filtered)})
        .attr('r', 10);
});
