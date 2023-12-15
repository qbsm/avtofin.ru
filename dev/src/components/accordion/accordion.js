export default () => {
    
    $(document).ready(() => {

        $('.accordion__item').on('click', function handleClickAccordionTitle(e) {
            e.preventDefault();
        
            $(this).toggleClass('active');
        
          })
    });
}
