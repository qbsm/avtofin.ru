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

        $('.js-thumb-up').on('click', function() {
            if (!$(this).hasClass('active')) {
                const cnt = $(this).parent().parent().find('.js-likes-count:first');
                const val = Number(cnt.html());
                cnt.html(val+1);
                $(this).addClass('active')
                $(this).parent().parent().find('.js-thumb-down').removeClass('active');
            }
            return false;
        });
        $('.js-thumb-down').on('click', function(e) {
            if (!$(this).hasClass('active')) {
                const cnt = $(this).parent().parent().find('.js-likes-count:first');
                const val = Number(cnt.html());
                cnt.html(val-1);
                $(this).addClass('active')
                $(this).parent().parent().find('.js-thumb-up').removeClass('active');
            }
            return false;
        });

    });
}
