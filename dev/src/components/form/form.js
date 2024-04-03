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
    if (typeof(ym)==='function') {
      ym(93699016,'reachGoal','modalForm');
    }

    $.ajax({
      type: "POST",
      url: 'form.php',
      data: data,
      success: (res) => {
        cb(res);
      }
    })
  }

  function clearForm(){
    $('input[name="Имя"]').val('');
    $('input[name="Телефон"]').val('');
  }

  $('.js-form').on('submit', function handleSubmitForm(e) {

    const $this = $(this);
    const $button = $this.find('.js-send');
    let data = $this.serialize();

    data += `&url=${document.location.href}`;

    if(!$(this).find('input[name=Телефон]').val()) return false;

    $button.removeClass('js-success');
    $button.addClass('js-sending');
    $button.attr('disabled', true);

    sendForm(data, () => {
      setTimeout(() => {
        $button.removeClass('js-sending');
        $button.addClass('js-success');
        clearForm();
      }, 1000);

      setTimeout(() => {
        $button.attr('disabled', false);
      }, 3000000);
    })

    return false;
  })
};
