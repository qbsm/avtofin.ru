/* global $ */
import linkTo from 'modules/link-to';

/*eslint-disable */
import modal from 'modal/modal';
import form from 'form/form';

<% _.forEach(modules, function(module) { %>import <%- module %> from '<%- module %>/<%- module %>';<% }); %>
/*eslint-enable */


require('es6-promise/auto');

$(document).ready(() => {
  linkTo(0);
  modal();
  form();
  <% _.forEach(modules, function(module) { %> <%- module %>();<% }); %>
});
