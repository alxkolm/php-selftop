var Backbone = require('backbone');
var ModalTemplate = require('./template.html');
var _ = require('underscore');

module.exports = Backbone.View.extend({
    className: 'ui small modal',
    render: function () {
        this.$el.html(_.template(ModalTemplate)());

        this.addForm = this.$el.find('#form-add-task');
        // bind callback to form submit
        this.addForm.submit($.proxy(this.onSubmit, this));

        return this;
    },
    onSubmit: function (e) {
        e.preventDefault();
        $.ajax('/api/tasks', {
            type: 'post',
            data: this.addForm.serialize(),
            success: function (reply) {
                app.tasks.add(reply);
            }
        })
    }
});