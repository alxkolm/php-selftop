var Backbone = require("backbone");
var Template = require('./templates/filter-date.html');
var _        = require('underscore');

module.exports = Backbone.View.extend({
    render: function () {
        this.$el.html(_.template(Template)());
        this.from = this.$el.find('#filter-date-input-from');
        this.to = this.$el.find('#filter-date-input-to');
        // bind events
        this.$el.find('.filter-date-input').change((e) => {
            e.preventDefault();
            app.trigger('filter:date:change', {from: this.from.val(), to: this.to.val()});
        });
        return this;
    }
});