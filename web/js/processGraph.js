$(function(){
    console.log(d3.max(window.dashboardProcessInfo, function(a){return a.keys}));
    console.log(d3.max(window.dashboardProcessInfo, function(a){return a.motions_filtered}));
    console.log(window.dashboardProcessInfo);
    var height = 210*2;
    var width = 297*2;
    var x = d3.scale.linear()
        .domain([
            0,
            d3.max(window.dashboardProcessInfo, function(a){return a.keys})
        ])
        .range([0, width - 15]);
    var y = d3.scale.linear()
        .domain([
            0,
            d3.max(window.dashboardProcessInfo, function(a){return a.motions_filtered})
        ])
        .range([0, height - 15]);
    var r = d3.scale.linear()
        .domain([0, d3.max(window.dashboardProcessInfo, function(a){return a.duration})])
        .range([0, 20]);

    var svg = d3.select('#graph-process').append('svg')
        .attr('width', width)
        .attr('height', height);

    svg.selectAll('circle')
        .data(window.dashboardProcessInfo)
        .enter()
        .append('circle')
        .attr('cx', function (d) {return x(d.keys);})
        .attr('cy', function (d) {return height - y(d.motions_filtered)})
        .attr('r', function (d) {return r(d.duration)})
        .style("fill", "steelblue");
});
