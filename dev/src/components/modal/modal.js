import 'magnific-popup';

export default () => {
  $(document).on('click', '.js-show-modal', function handleClickTriggerModal(e) {
    e.preventDefault();

    const $this = $(this);
    const id = $this.attr('data-modal');

    if (id === '#modal-1') {
      const attrModalTitle = $this.attr('data-modal-title');
      const modalTitle = attrModalTitle ? attrModalTitle : 'Отправьте заявку и мы перезвоним';

      $(id).find('.modal__title').html(modalTitle);
    }

    $.magnificPopup.open({
      items: {
          src: id,
          type: 'inline'
      },
      midClick: true,
      removalDelay: 300,
      fixedContentPos: true,
      mainClass: 'mfp-fade'
    });
  })
};
