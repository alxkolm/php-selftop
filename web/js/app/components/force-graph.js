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
    require('../css/axis.css');
    require('../css/force-graph.css');
    require('../css/d3tip.css');
    $.fn.forceGraph = function (options) {
        var nodes = options.nodes.slice(0);
        var links = options.links.slice(0);

        // filter weak links
        //links = links.filter((a) => {
        //    return a.value > 1
        //});

        // filter nodes without links
        var nodesWithLink = links.reduce((out, link) => {
            out.push(link.source);
            out.push(link.target);
            return out;
        }, []);
        nodesWithLink = nodesWithLink.filter((value, index, self) => {
            return self.indexOf(value) === index;
        });

        nodes = nodes.filter((a) => {
            return nodesWithLink.indexOf(a.id) != -1;
        });

        // Convert id to index
        var nodeIds = nodes.map((a) => {
            return a.id;
        });
        links = links.map((a) => {
            return {
                source: nodeIds.indexOf(a.source),
                target: nodeIds.indexOf(a.target),
                value: a.value
            }
        });

        var width  = 960,
            height = 500;

        var color = d3.scale.category20();

        var maxValue = d3.max(links, (a)=>{return a.value});

        var force = d3.layout.force()
            .charge(-120)
            .linkDistance(30)
            .linkStrength(function(d){
                var strength = 0.7 + 0.3 * (d.value / maxValue);

                // amplify strength for nodes with same class
                strength = (d.source.cluster === d.target.cluster) ? strength *= 10 : strength;

                return strength;
            })
            .size([width, height]);

        var svg = d3.select(this[0]).append("svg")
            .attr("width", width)
            .attr("height", height);

        force
            .nodes(nodes)
            .links(links)
            .start();

        var link = svg.selectAll(".link")
            .data(links)
            .enter().append("line")
            .attr("class", "link")
            .style("stroke-width", function (d) {
                return Math.sqrt(d.value);
            });

        // init d3tip
        var tip = d3.tip().attr('class', 'd3-tip').html(function(d) { return d.title; });

        svg.call(tip);

        var node = svg.selectAll(".node")
            .data(nodes)
            .enter().append("circle")
            .attr("class", "node")
            .attr("r", function(d){return 3 + Math.sqrt(5*d.weight);})
            .style("fill", function (d) {
                return color(d.cluster);
            })
            .on('mouseover', tip.show)
            .on('mouseout', tip.hide)
            .call(force.drag);

        node.append("title")
            .text(function (d) {
                return d.name;
            });

        force.on("tick", function () {
            link.attr("x1", function (d) {
                    return d.source.x;
                })
                .attr("y1", function (d) {
                    return d.source.y;
                })
                .attr("x2", function (d) {
                    return d.target.x;
                })
                .attr("y2", function (d) {
                    return d.target.y;
                });

            node.attr("cx", function (d) {
                    return d.x;
                })
                .attr("cy", function (d) {
                    return d.y;
                });
        });



        //this[0].update = $.proxy(update, this);
    };
}));
