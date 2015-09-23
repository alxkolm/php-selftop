var Backbone = require('backbone');
var ModalTemplate = require('./template.html');
var _ = require('underscore');

module.exports = Backbone.View.extend({
    className: 'ui small modal',
    render: function () {
        this.$el.html(_.template(ModalTemplate)());

        return this;
    }
});