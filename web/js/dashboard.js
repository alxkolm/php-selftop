
dashboard = {
    /**
     * Common colors of processes for all charts
     */
    processColor: d3.scale.category20(),
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


