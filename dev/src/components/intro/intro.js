export default () => {
    function changeImage() {
        const el = $('.js-img-change');
        const last = el.attr('data-last');
        const current = Number(el.attr('data-current'));
        let next = current + 1;
        if (next > last) {
            next = 1;
        }
        let src = el.attr('src');
        src = src.replace(current, next);
        el.attr('src', src);
        el.attr('data-current', next);
    }

    $(document).ready(function () {
       const delay = $('.js-img-change').attr('data-delay');
       if (delay) {
           setInterval(changeImage, Number(delay));
       }
    });
}
