/* global $ */
import linkTo from 'modules/link-to';

/*eslint-disable */
import modal from 'modal/modal';
import form from 'form/form';

import header from 'header/header';import nav from 'nav/nav';import intro from 'intro/intro';import calc from 'calc/calc';import actions from 'actions/actions';import advantages from 'advantages/advantages';import reviews from 'reviews/reviews';import accordion from 'accordion/accordion';import branches from 'branches/branches';import conditions from 'conditions/conditions';import footer from 'footer/footer';
/*eslint-enable */


require('es6-promise/auto');

$(document).ready(() => {
  linkTo(0);
  modal();
  form();
   header(); nav(); intro(); calc(); actions(); advantages(); reviews(); accordion(); branches(); conditions(); footer();
});
