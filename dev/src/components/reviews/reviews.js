export default () => {
    
    $(document).ready(() => {

        new Swiper('#reviews-swiper', {
            direction: 'horizontal',
            loop: true,
            allowTouchMove: true,
            breakpoints: {
                320: {
                    slidesPerView: 1,
                    spaceBetween: 20
                },
                992: {
                    slidesPerView: 6,
                    spaceBetween: 20
                }
            },
            pagination: {
                el: '.swiper-pagination',
                type: 'bullets',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            }
        });
    });
}