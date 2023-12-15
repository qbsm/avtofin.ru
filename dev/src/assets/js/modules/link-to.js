/*global $*/

export default function (offsetVal = 0, speed = 1000) {
  /*===============================================================
   * Вспомогательные CSS классы
   * =============================================================*/
  const classNames = {
    link: 'js-link-to',
    header: 'js-header',
  };

  function findPos(elem) {
    let offsetTop = 0;
    do {
      if (!isNaN( elem.offsetTop)) {
        offsetTop += elem.offsetTop;
      }
    } while(elem = elem.offsetParent);
    return offsetTop;
  }

  function scrollTo(e) {
    e.preventDefault();
    let offset = offsetVal;
    const $header = $(`.${classNames.header}`);
    const selector = $(this).data('href');
    const $targetBlock = $(selector);
    const isFiexedHeader = $header.css('position') === 'fixed';
    const scroll = $(window).scrollTop();

    if (isFiexedHeader) {
      offset -= $header.outerHeight();
    }

    const distance = findPos($targetBlock[0]) + offset;
    let time = (Math.abs(distance - scroll) / speed) * 1000;

    if (time > 2000) {
      time = 2000;
    }


    $('html, body').animate({
      scrollTop: distance,
    }, time);
  }

  $(`.${classNames.link}`).on('click', scrollTo);
}
