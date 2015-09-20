
dashboard = {
    /**
     * Common colors of processes for all charts
     */
    processColor: d3.scale.ordinal().range(colorbrewer.Set1[9]),
    clusterColor: d3.scale.ordinal().range(colorbrewer.Set3[12]),
    taskColor:    d3.scale.ordinal().range(colorbrewer.Set2[8]),

    /**
     * Time domain for all charts
     */
    timeExtent: getCommonTimeDomain(),

    /**
     * Custom tick format
     */
    tickFormat: d3.time.format.multi([
        [".%L", function(d) { return d.getMilliseconds(); }],
        [":%S", function(d) { return d.getSeconds(); }],
        ["%H:%M", function(d) { return d.getMinutes(); }],
        ["%H", function(d) { return d.getHours(); }],
        ["%a %d", function(d) { return d.getDay() && d.getDate() != 1; }],
        ["%b %d", function(d) { return d.getDate() != 1; }],
        ["%B", function(d) { return d.getMonth(); }],
        ["%Y", function() { return true; }]
    ]),

    /**
     * Format time duration to com[act string
     * @param seconds
     * @returns {string}
     */
    formatDuration: function (seconds) {
        var result = '';
        if (seconds < 1) {
            result = '< 1s';
        } else if (seconds < 60) {
            result = Math.floor(seconds) + 's';
        } else if (seconds < 60 * 60) {
            min    = Math.floor(seconds / 60);
            sec    = Math.floor(seconds - (min * 60));
            result = min + 'm' + sec + 's';
        } else if (seconds < 60*60*24) {
            hour   = Math.floor(seconds / (60 * 60));
            min    = Math.floor((seconds - hour * 60 * 60) / 60);
            sec    = Math.floor(seconds - (hour * 60 * 60) - (min * 60));
            result = hour + 'h' + min + 'm' + sec + 's';
        } else {
            day    = Math.floor(seconds / (60 * 60 * 24));
            hour   = Math.floor((seconds - day * 60 * 60 * 24) / 60);
            min    = Math.floor((seconds - hour * 60 * 60) / 60);
            sec    = Math.floor(seconds - (day * 60 * 60 * 24) - (hour * 60 * 60) - (min * 60));
            result = day + 'd' + hour + 'h' + min + 'm' + sec + 's';
        }
        return result;
    }
};

/**
 * Calculate common time domain
 * @returns {*[]}
 */
function getCommonTimeDomain(timelineData, keysData) {
    keysData = keysData || dashboardKeys;
    timelineData   = timelineData || dashboardTimeline;
    var extent     = timeLineExtent(timelineData);

    if (typeof(keysData) != 'undefined'){
        var keysFiltered = keysData.filter(function (d) {
            return d.count > 0
        });
        var extent3 = d3.extent(keysFiltered, function(a){return new Date(a.date)});

        extent = [
            Math.min(extent[0], extent3[0]),
            Math.max(extent[1], extent3[1])
        ];
    }

    return extent;
}

function timeLineExtent(timelineData) {
    var extent1 = d3.extent(timelineData, function(a){return new Date(a.start)});
    var extent2 = d3.extent(timelineData, function(a){return new Date(a.end)});
    return [
        Math.min(extent1[0], extent2[0]),
        Math.max(extent1[1], extent2[1])
    ];
}