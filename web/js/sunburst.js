$(function(){
    var width = 400,
        height = 400,
        radius = Math.min(width, height) / 2,
        color = dashboard.processColor;

    var svg = d3.select("#sunburst").append("svg")
        .attr("width", width)
        .attr("height", height)
        .append("g")
        .attr("transform", "translate(" + width / 2 + "," + height * .5 + ")");

    var partition = d3.layout.partition()
        .sort(null)
        .size([2 * Math.PI, radius * radius])
        .value(function(d) { return d.size; });

    var arc = d3.svg.arc()
        .startAngle(function(d) { return d.x; })
        .endAngle(function(d) { return d.x + d.dx; })
        .innerRadius(function(d) { return Math.sqrt(d.y); })
        .outerRadius(function(d) { return Math.sqrt(d.y + d.dy); });

    var nodes = partition.nodes(dashboardDurations)
        .filter(function(d) {
            return (d.dx > 0.01); // 0.005 radians = 0.29 degrees
        });
    var path = svg.datum(dashboardDurations).selectAll("path")
        .data(nodes)
        .enter().append("path")
        .attr("display", function(d) { return d.depth ? null : "none"; }) // hide inner ring
        .attr("d", arc)
        .style("stroke", "#fff")
        .style("fill", function(d) { return d.depth == 1 ? color(d.process_id) : color(d.name); })
        //.style("fill", function(d) { return color((d.children ? d : d.parent).name); })
        .style("fill-rule", "evenodd")
        .on("mouseover", mouseover)
        .on("mouseleave", mouseleave);
    totalSize = path.node().__data__.value;
});

function mouseover(d){
    var percentage = (100 * d.value / totalSize).toPrecision(3);
    var percentageString = percentage + "%";
    if (percentage < 0.1) {
        percentageString = "< 0.1%";
    }
    if (d.depth == 1) {
        colorStripDim(d.process_id);
    } else if (d.depth == 2) {
        colorStripDimByWindow(d.window_id);
    }
    d3.select("#sunburst .percentage")
        .text(percentageString);
    d3.select("#sunburst .duration")
        .text(dashboard.formatDuration(d.value));
    d3.select("#sunburst .window")
        .text(d.name);
}

function mouseleave(){
    colorStripUndim();
}