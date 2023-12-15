/* global $ */
import linkTo from 'modules/link-to';

/*eslint-disable */
import modal from 'modal/modal';
import form from 'form/form';

import actions from 'actions/actions';import advantages from 'advantages/advantages';import reviews from 'reviews/reviews';import accordion from 'accordion/accordion';import branches from 'branches/branches';import conditions from 'conditions/conditions';
/*eslint-enable */


require('es6-promise/auto');

$(document).ready(() => {
  linkTo(0);
  modal();
  form();
   actions(); advantages(); reviews(); accordion(); branches(); conditions();
});
