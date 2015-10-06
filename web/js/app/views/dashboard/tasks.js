var Backbone = require('backbone');
var _ = require('underscore');
var $ = require('jquery');
var tasksTpl = require('./templates/tasks.html');

module.exports = Backbone.View.extend({
    render: function(){
        this.$el.html(_.template(tasksTpl)({tasks: this.collection}));
        return this;
    }
});