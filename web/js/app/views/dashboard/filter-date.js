var Backbone = require("backbone");
var Template = require('./templates/filter-date.html');
var _        = require('underscore');

module.exports = Backbone.View.extend({
    render: function () {
        this.$el.html(_.template(Template)());
        return this;
    }
});