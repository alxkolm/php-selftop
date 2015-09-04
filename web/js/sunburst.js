$.fn.extend({
    sunburst: function (options) {
        var width = 400,
            height = 400,
            radius = Math.min(width, height) / 2,
            color = options.color,
            data  = options.data,
            that  = this;

        var markup = '<div class="sunburst-chart sunburst-side-left">'
            + '<div class="sunburst-info">'
            + '<div class="sunburst-percentage"></div>'
            + '<div class="sunburst-duration"></div>'
            + '<div class="sunburst-window"></div>'
            + '</div>'
            + '</div>'
            + '<div class="sunburst-process sunburst-side-right"></div>';
        this.append(markup);
        this.addClass("sunburst");

        var svg = d3.select(this.find('.sunburst-chart')[0]).append("svg")
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

        var nodes = partition.nodes(data)
            .filter(function(d) {
                return (d.dx > 0.01); // 0.005 radians = 0.29 degrees
            });
        var path = svg.datum(data).selectAll("path")
            .data(nodes)
            .enter().append("path")
            .attr("display", function(d) { return d.depth ? null : "none"; }) // hide inner ring
            .attr("d", arc)
            .attr("id", function (d) {return d.depth == 1 ? 'process-' + d.process_id : 'window-' + d.window_id;})
            .style("stroke", "#fff")
            .style("fill", function(d) {
                switch (d.depth){
                    case 0:
                        return '#000000';
                    case 1:
                        return color(d.process_id);
                    case 2:
                        var shiftColorStart = d3.hcl(color(d.parent.process_id));
                        shiftColorStart.c = 100;
                        var shiftColorEnd = d3.hcl(color(d.parent.process_id));
                        //shiftColorEnd.h += 40;
                        shiftColorEnd.c = 10;
                        shiftColorEnd.l = 90;

                        var childColor = d3.scale.linear()
                            .range([
                                shiftColorEnd,
                                shiftColorStart
                            ])
                            .domain([
                                d3.min(d.parent.children, function(a){return a.value}),
                                d3.max(d.parent.children, function(a){return a.value})
                            ])
                            .interpolate(d3.interpolateHcl);

                        return childColor(d.value);
                    default:

                }

                return d.depth == 1 ? color(d.process_id) : color(d.name);
            })
            //.style("fill", function(d) { return color((d.children ? d : d.parent).name); })
            .style("fill-rule", "evenodd")
            .on("mouseover", mouseover)
            .on("mouseleave", mouseleave);

        totalSize = path.node().__data__.value;

        var textNodes = nodes.filter(function(d){return d.depth == 1 && d.dx > 0.5});
        svg.selectAll('text')
            .data(textNodes)
            .enter()
            .append('text')
            .attr('x', 0)
            .attr('dy', '30')
            .attr('text-anchor', 'middle')
            .attr('letter-spacing', '0.25em')
            .style('fill', function(d) {
                var c = d3.hcl(color(d.process_id));
                c.l = c.l > 80 ? c.l = 0 : c.l;
                return c.brighter(3);
            })
            .on("mouseover", mouseover)
            .on("mouseleave", mouseleave)
            .append('textPath')
            .attr("startOffset",function(d){return '25%';})
            //.attr('stroke', 'black')
            .attr('xlink:href', function (d) {return '#' + (d.depth == 1 ? 'process-' + d.process_id : 'window-' + d.window_id);})
            .text(function (d) {
                var percentage = (100 * d.value / totalSize).toPrecision(2);
                return d.name + ' (' + percentage +'%)';
            });

        /**
         * Mouse move callback
         * @param d
         */
        function mouseover(d){
            var percentage = (100 * d.value / totalSize).toPrecision(3);
            var percentageString = percentage + "%";
            if (percentage < 0.1) {
                percentageString = "< 0.1%";
            }

            d3.select(that.find(".sunburst-percentage")[0])
                .text(percentageString);
            d3.select(that.find(".sunburst-duration")[0])
                .text(dashboard.formatDuration(d.value));
            d3.select(that.find(".sunburst-window")[0])
                .text(d.name);

            if (d.depth == 1){
                drawProcessList(d);
            }

            // Execute callback
            if (typeof options.mouseover != 'undefined'){
                options.mouseover(d);
            }
        }

        /**
         * Mouse leave callback
         */
        function mouseleave(d){
            // Execute callback
            if (typeof options.mouseleave != 'undefined') {
                options.mouseleave(d);
            }
        }

        /**
         * Draw process list
         * @param data
         */
        function drawProcessList(data){
            var el = that.find('.sunburst-process')[0];
            d3.select(el).selectAll('.process').remove();
            var processEl = d3.select(el).selectAll('.process')
                .data(data.children)
                .enter()
                .append('div')
                .attr('class', 'process');
            processEl.append('span')
                .attr('class', 'duration')
                .text(function (d) {return dashboard.formatDuration(d.value)});
            processEl.append('span')
                .attr('class', 'name')
                .text(function (d) {return d.name});
        }
    }
});

$(function(){
    $('#sunburst').sunburst({
        color: dashboard.processColor,
        data: dashboardDurations,
        mouseleave: colorStripUndim,
        mouseover: function (d) {
            if (d.depth == 1) {
                colorStripDim(d.process_id);
            } else if (d.depth == 2) {
                colorStripDimByWindow(d.window_id);
            }
        }
    });
    $('#sunburst-clusters').sunburst({
        color: dashboard.clusterColor,
        data: dashboardClustersDurations
    });
});

