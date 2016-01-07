var Backbone = require("backbone");
var Template = require('./templates/filter-date.html');
var _        = require('underscore');

module.exports = Backbone.View.extend({
    render: function () {
        this.$el.html(_.template(Template)());
        this.date = this.$el.find('#filter-date-input');
        this.date[0].valueAsDate = new Date();
        // bind events
        this.$el.find('.filter-date-input').change((e) => {
            e.preventDefault();
            app.trigger('filter:date:change', {date: this.date.val()});
        });
        return this;
    }
});