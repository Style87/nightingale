/***
 *  Exposes
 *  Consumes
 */
 var _ = require('underscore');
 var Backbone = require('backbone');
 import template from '../templates/BaseModalTemplate.js';
 import { uuid } from './uuid';

var BaseModalView = Backbone.View.extend({
 childViews: {},
 template: _.template(template),
 
 initialize: function(options) {
   this.options = _.extend({
     id: uuid(),
     title: "Confirm",
     body: "",
     showCloseButton: true,
     closeButtonClass: 'btn-default',
     closeButtonText: 'Close',
     showAffirmButton: true,
     affirmButtonClass: 'btn-primary',
     affirmButtonText: 'ok',
     closeOnAffirm: false,
     el: 'body'
   }, options);
   
   this.el = this.options.el;
   
   this.listenTo(this.model, "change", this.render);
 },
 
 close : function(){
   if(this.childViews.length > 0){
     for (var key in this.childViews)
     {
       this.childViews[key].close();
     }
   }
   this.remove();
 },
 remove: function() {
     this.unbind();
     this.stopListening();
     this.$el.remove();
     return this;
 },
 render : function(){
   this.beforeRender.apply(this);
   this.$el = $(this.template(this));
   $(this.el).append(this.$el);
   if(Object.keys(this.childViews).length > 0){
     for (var key in this.childViews)
     {
       this.childViews[key].render();
     }
   }
   this.delegateEvents();

   this.afterRender.apply(this);

   return this;
 },
 beforeRender: function(){
   if (this.hasOwnProperty('options') && this.options.hasOwnProperty('beforeRender') && typeof this.options.beforeRender === 'function') {
     this.options.beforeRender.apply(this);
   }
 },
 afterRender: function(){
   if (this.hasOwnProperty('options') && this.options.hasOwnProperty('afterRender') && typeof this.options.afterRender === 'function') {
     this.options.afterRender.apply(this);
   }
 },
 
 show: function(){
   $('#'+this.options.id).modal('show');
 },
});

export default BaseModalView;