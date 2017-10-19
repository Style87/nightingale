/***
 *  Exposes
 *  Consumes
 */
import 'jquery';
var _ = require('underscore');
var Backbone = require('backbone');
import template from '../templates/ContentTemplate.js';

var ContentView = Backbone.View.extend({
  el: '#wrapper',

  template: _.template(template),

  render: function () {
    $(this.$el).append(this.template);
  }
});

export default ContentView;