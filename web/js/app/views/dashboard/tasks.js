var Backbone = require('backbone');
var _ = require('underscore');
var $ = require('jquery');
var tasksTpl = require('./templates/tasks.html');

module.exports = Backbone.View.extend({
    initialize: function () {
        this.collection.on('update', $.proxy(this.render, this));
    },
    render: function(){
        this.$el.html(_.template(tasksTpl)({tasks: this.collection}));
        this.$el.find('.c-remove').click(this.removeTask);
        return this;
    },
    removeTask: function(e) {
        e.preventDefault();
        var el = $(e.currentTarget);
        var taskId = el.attr('task-id');
        $.ajax('/api/tasks/' + taskId, {
            type: 'DELETE',
            success: function(){
                app.tasks.remove(taskId);
            }
        });
    }
});