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
            showLabels = options.showLabels || false,
            that  = this;

        var markup = '<div class="sunburst-chart">'
            + '<div class="sunburst-info">'
            + '<div class="sunburst-percentage"></div>'
            + '<div class="sunburst-duration"></div>'
            + '<div class="sunburst-window"></div>'

            + '</div>'
            + '<div class="sunburst-holder"></div>'
            + '</div>';
        this.append(markup);
        this.addClass("sunburst");

        var svg = d3.select(this.find('.sunburst-holder')[0]).append("svg")
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



        var drag = d3.behavior.drag();
        drag.on('dragstart', dragstart);
        drag.on('dragend', dragend);

        function draw(data) {
            var nodes = partition.nodes(data)
                .filter(function(d) {
                    return (d.dx > 0.01); // 0.005 radians = 0.29 degrees
                });

            var path = svg.selectAll("path")
                .data(nodes);

            // move exist elements
            path
                .attr("d", arc)
                .style("fill", fillColorFn)
                .attr("id", function (d) {return d.depth == 1 ? 'sector-' + d.sector_id : 'window-' + d.window_id;});


            // draw new elements
            path.enter().append("path")
                .attr("display", function(d) { return d.depth ? null : "none"; }) // hide inner ring
                .attr("d", arc)
                .attr("id", function (d) {return d.depth == 1 ? 'sector-' + d.sector_id : 'window-' + d.window_id;})
                .style("stroke", "#262626")
                .style("fill", fillColorFn)
                //.style("fill", function(d) { return color((d.children ? d : d.parent).name); })
                .style("fill-rule", "evenodd")
                .on("mouseover", mouseover)
                .on("mouseleave", mouseleave)
                .on("click", onclick)
                .call(drag);


            path.exit().remove();

            totalSize = path.node().__data__.value;

            if (showLabels) {
                var textNodes = nodes.filter(function(d){return d.depth == 1 && d.dx > 0.5});

                var text = svg.selectAll('text')
                    .data(textNodes, function (d) {return d.sector_id });

                // Change exist elements
                //text
                //    .attr("startOffset",function(d){return '25%';})
                //    .attr('xlink:href', function (d) {return '#' + (d.depth == 1 ? 'sector-' + d.sector_id : 'window-' + d.window_id);})
                //    .text(function (d) {
                //        var percentage = (100 * d.value / totalSize).toPrecision(2);
                //        return d.name + ' (' + percentage +'%)';
                //    });

                text
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
                    .on("click", onclick)
                    .append('textPath')
                    .attr("startOffset",function(d){return '25%';})
                    .attr('xlink:href', function (d) {return '#' + (d.depth == 1 ? 'sector-' + d.sector_id : 'window-' + d.window_id);})
                    .text(function (d) {
                        var percentage = (100 * d.value / totalSize).toPrecision(2);
                        return d.name + ' (' + percentage +'%)';
                    });

                //text.exit().remove();
            }
        }

        draw(data);

        showTotalText();

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
            showTotalText();

            // Execute callback
            if (typeof options.mouseleave != 'undefined') {
                options.mouseleave(d, this);
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
        function onclick(d){
            console.log('click!');
            // Execute callback
            if (typeof options.onclick != 'undefined') {
                options.onclick(d, this);
            }
        }

        function update(data) {
            draw(data);
            showTotalText();
        }


        function fillColorFn(d) {
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
        }

        function showTotalText(){
            d3.select(that.find(".sunburst-percentage")[0])
                .text('100%');
            d3.select(that.find(".sunburst-duration")[0])
                .text(dashboard.formatDuration(totalSize));
            d3.select(that.find(".sunburst-window")[0])
                .text('Total');
        }

        this[0].update = $.proxy(update, this);
    };
}));
