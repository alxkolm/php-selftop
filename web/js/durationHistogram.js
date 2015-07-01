$(function(){
    // Generate a log-normal distribution with a median of 30 minutes.
    var values = dashboardDurations;
//var values = d3.range(1000).map(d3.random.logNormal(Math.log(30), .4));

// Formatters for counts and times (converting numbers to Dates).
    var formatCount = d3.format(",.0f"),
        formatTime = d3.time.format("%M:%S"),
        formatMinutes = function(d) { return formatTime(new Date(2012, 0, 1, 0, 0,d)); };

    var margin = {top: 5, right: 15, bottom: 20, left: 15},
        width = (297*2) - margin.left - margin.right,
        height = (210*2) - margin.top - margin.bottom;

    var x = d3.scale.linear()
        .domain([0, 120])
        .range([0, width]);

// Generate a histogram using twenty uniformly-spaced bins.
    var data = d3.layout.histogram()
        .bins(x.ticks(20))
    (values);

    var y = d3.scale.linear()
        .domain([0, d3.max(data, function(d) { return d.y; })])
        .range([height, 0]);

    var xAxis = d3.svg.axis()
        .scale(x)
        .orient("bottom")
        .tickFormat(formatMinutes);

    var svg = d3.select("#graph-duration-histrogram").append("svg")
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom)
        .append("g")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

    var bar = svg.selectAll(".bar")
        .data(data)
        .enter().append("g")
        .attr("class", "bar")
        .attr("transform", function(d) { return "translate(" + x(d.x) + "," + y(d.y) + ")"; });

    bar.append("rect")
        .attr("x", 1)
        .attr("width", x(data[0].dx) - 1)
        .attr("height", function(d) { return height - y(d.y); });

    bar.append("text")
        .attr("dy", ".75em")
        .attr("y", 6)
        .attr("x", x(data[0].dx) / 2)
        .attr("text-anchor", "middle")
        .text(function(d) { return formatCount(d.y); });

    svg.append("g")
        .attr("class", "x axis")
        .attr("transform", "translate(0," + height + ")")
        .call(xAxis);
});
