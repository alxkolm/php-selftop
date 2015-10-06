var Backbone = require('backbone');
var _ = require('underscore');
var $ = require('jquery');
var tasksTpl = require('./templates/tasks.html');

module.exports = Backbone.View.extend({
    initialize: function () {
        this.collection.on('add', $.proxy(this.render, this));
    },
    render: function(){
        this.$el.html(_.template(tasksTpl)({tasks: this.collection}));
        return this;
    }
});