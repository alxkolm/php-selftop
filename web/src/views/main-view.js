var Backbone = require("backbone");

module.exports = Backbone.View.extend({
    renderPage: function (view) {
        return this.$el.html(view.render().el);
    }
});
