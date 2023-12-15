/* global $ */
const Formatter = require('formatter.js/dist/formatter');

export default (opt) => {
  $('[name="Телефон"]').on('focus', function handleFocus() {
    const $this = $(this);
    if (!$this.hasClass('formatted')) {
      const formatted = new Formatter(this, {
        pattern: '+7 ({{999}}) {{999}}-{{99}}-{{99}}',
        persistent: true,
      });
      $this.addClass('formatted');
      $this.focus();
    }
  });

  function sendForm(data, cb){
    $.ajax({
      type: "POST",
      url: 'form.php',
      data: data,
      success: (res) => {
        cb(res);
      }
    })
  }

  $('.js-form').on('submit', function handleSubmitForm(e) {

    const $this = $(this);
    const $button = $this.find('.js-send');
    let data = $this.serialize();

    data += `&url=${document.location.href}`;

    $button.removeClass('js-success');
    $button.addClass('js-sending');

    sendForm(data, () => {
      setTimeout(() => {
        $button.removeClass('js-sending');
        $button.addClass('js-success');
      }, 1000);

    })

    return false;
  })
};
