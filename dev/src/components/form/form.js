/* global $ */
const Formatter = require('formatter.js/dist/formatter');

export default (opt) => {
  function validatePhone(phone) {
    const phoneRegex = /^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/;
    return phoneRegex.test(phone);
  }
  function validateName(name) {
    const nameRegex = /^[A-zА-я -]+$/;
    return nameRegex.test(name);
  }

  $('.js-form-next').on('click', function() {
    const nameCorrect = validateName($('.js-form-name').val());
    const phoneCorrect = validatePhone($('.js-form-phone').val());
    if (nameCorrect) {
      $('.js-form-name-error').addClass('hidden');
      $('.js-form-name').removeClass('invalid');
    } else {
      $('.js-form-name-error').removeClass('hidden');
      $('.js-form-name').addClass('invalid');
    }
    if (phoneCorrect) {
      $('.js-form-phone-error').addClass('hidden');
      $('.js-form-phone').removeClass('invalid');
    } else {
      $('.js-form-phone-error').removeClass('hidden');
      $('.js-form-phone').addClass('invalid');
    }
    if (!phoneCorrect || !nameCorrect) {
      return;
    }
    $('.js-form-part2').removeClass('hidden');
    $('.js-form-part1').addClass('hidden');
  });

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

  setTimeout(() => {
    $('.js-form-token').each(function fillToken() {
      $(this).val($(this).data('token'));
    });
  }, 10000);

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
