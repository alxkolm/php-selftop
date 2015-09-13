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
    require('../css/sunburst.css');
    $.fn.sunburst = function (options) {
        var width = 300,
            height = 300,
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
            + '<div class="sunburst-process-list sunburst-side-right"></div>';
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

        var drag = d3.behavior.drag();
        drag.on('dragstart', dragstart);
        drag.on('dragend', dragend);

        var path = svg.datum(data).selectAll("path")
            .data(nodes)
            .enter().append("path")
            .attr("display", function(d) { return d.depth ? null : "none"; }) // hide inner ring
            .attr("d", arc)
            .attr("id", function (d) {return d.depth == 1 ? 'sector-' + d.sector_id : 'window-' + d.window_id;})
            .style("stroke", "#262626")
            .style("fill", function(d) {
                switch (d.depth){
                    case 0:
                        return '#000000';
                    case 1:
                        return color(d.sector_id);
                    case 2:
                        var shiftColorStart = d3.hcl(color(d.parent.sector_id));
                        shiftColorStart.c = 100;
                        var shiftColorEnd = d3.hcl(color(d.parent.sector_id));
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

                return d.depth == 1 ? color(d.sector_id) : color(d.name);
            })
            //.style("fill", function(d) { return color((d.children ? d : d.parent).name); })
            .style("fill-rule", "evenodd")
            .on("mouseover", mouseover)
            .on("mouseleave", mouseleave)
            .call(drag);

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
                var c = d3.hcl(color(d.sector_id));
                c.l = c.l > 80 ? c.l = 0 : c.l;
                return c.brighter(3);
            })
            .on("mouseover", mouseover)
            .on("mouseleave", mouseleave)
            .append('textPath')
            .attr("startOffset",function(d){return '25%';})
            //.attr('stroke', 'black')
            .attr('xlink:href', function (d) {return '#' + (d.depth == 1 ? 'sector-' + d.sector_id : 'window-' + d.window_id);})
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
            //d3.select(that.find(".sunburst-window")[0])
            //    .text(d.name);

            if (d.depth == 1){
                //drawProcessList(d);
            }
            // Execute callback
            if (typeof options.mouseover != 'undefined'){
                options.mouseover(d, this);
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
            var el = that.find('.sunburst-process-list')[0];
            d3.select(el).selectAll('.sunburst-process').remove();
            var processEl = d3.select(el).selectAll('.sunburst-process')
                .data(data.children)
                .enter()
                .append('div')
                .attr('class', 'sunburst-process');
            processEl.append('span')
                .attr('class', 'sunburst-duration')
                .text(function (d) {return dashboard.formatDuration(d.value)});
            processEl.append('span')
                .attr('class', 'sunburst-name')
                .text(function (d) {return d.name});
        }

        function dragstart(d){
            // Execute callback
            if (typeof options.dragstart != 'undefined') {
                options.dragstart(d);
            }
        }
        function dragend(d){
            // Execute callback
            if (typeof options.dragend != 'undefined') {
                options.dragend(d);
            }
        }
    };
}));


//$(function(){
//
//    if (typeof dashboardClustersDurations != 'undefined'){
//        $('#sunburst-clusters').sunburst({
//            color: dashboard.clusterColor,
//            data: dashboardClustersDurations,
//            dragend: function (d) {
//                var el = $(d3.event.sourceEvent.toElement);
//                var taskId = el.attr('task-id');
//                var window_id;
//                switch (d.depth) {
//                    case 1:
//                        window_id = d.children.map(function (a) {
//                            return a.window_id;
//                        });
//                        break;
//                    case 2:
//                        window_id = [d.window_id];
//                        break;
//                }
//
//                $.ajax('/record/assign', {
//                    type: 'POST',
//                    data: {task: taskId, window: window_id},
//                    success: function(){
//                        el.css({backgroundColor: 'green'}).animate({backgroundColor: 'none'});
//                    }
//                });
//            }
//        });
//    }
//    if (typeof dashboardTaskDurations != 'undefined'){
//        $('#sunburst-task').sunburst({
//            color: dashboard.taskColor,
//            data: dashboardTaskDurations
//        });
//    }
//});

