var ViewIndex = require('../views/dashboard/index');

module.exports = function (options) {
    var app = options.app;
    return {
        index: function () {
            console.log('route: dashboard/index');
            var view = new ViewIndex;
            app.mainView.renderPage(view);
        }
    };
};