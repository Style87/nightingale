/*global define*/
var _ = require('underscore');
import BackboneModal from '../core/BackboneModal/BackboneModal.js';
import { template as TestDatabaseModalHeaderTemplate } from '../templates/TestDatabaseModalHeaderTemplate.js';
import { template as TestDatabaseModalBodyTemplate } from '../templates/TestDatabaseModalBodyTemplate.js';
import { template as TestDatabaseModalFooterTemplate } from '../templates/TestDatabaseModalFooterTemplate.js';

var TestDatabaseModalView = new BackboneModal({
  headerTemplate: _.template(TestDatabaseModalHeaderTemplate),
  bodyTemplate: _.template(TestDatabaseModalBodyTemplate),
  footerTemplate: _.template(TestDatabaseModalFooterTemplate),

  events: {},

  afterRender: function(){
    
  },
});

export default TestDatabaseModalView;