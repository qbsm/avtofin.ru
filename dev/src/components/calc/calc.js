export default () => {

    function pluralMonths(n) {
        const m10 = n % 10;
        const m100 = n % 100;
        if (m100 >= 11 && m100 <= 14) return 'месяцев';
        if (m10 === 1) return 'месяц';
        if (m10 >= 2 && m10 <= 4) return 'месяца';
        return 'месяцев';
    }

    function calc() {
        const rate = Number($('.js-rate-label').attr('data-rate'));
        const amount = Number($('.js-amount-range').val());
        const months = Number($('.js-months-range').val());
        const amountVal = String(amount).replace(/(.)(?=(\d{3})+$)/g,'$1 ')

        if ($('.js-amount-input').is(":focus")) {
            $('.js-amount-input').val(amount);
        } else {
            $('.js-amount-input').val(amountVal);
        }
        $('.js-months-input').val(months);
        $('.js-months-label').html(months);
        $('.js-months-plural').html(pluralMonths(months));

        const payment = Math.round(amount * rate/100 * months * 30);
        const paymentVal = String(payment).replace(/(.)(?=(\d{3})+$)/g,'$1 ')
        $('.js-payment-label').html(paymentVal);
    }

    $(document).ready(function() {
        calc();
        $('.js-amount-range').on('input', calc).on('change', calc);
        $('.js-months-range').on('input', calc).on('change', calc);
        $('.js-amount-input').on('focus', function() {
            $(this).val($(this).val().replaceAll(' ', ''));
        });
        $('.js-amount-input').on('focusout', function() {
            const amount = $(this).val();
            if (amount !== '') {
                $('.js-amount-range').val(amount.replaceAll(' ', '')).change();
            }
        });
        $('.js-amount-input').on('input', function(){
            const amount = $(this).val();
            if (amount !== '') {
                $('.js-amount-range').val(amount.replaceAll(' ', '')).change();
            }
        });
        $('.js-months-input').on('input', function(){
            const months = $(this).val();
            if (months !== '') {
                $('.js-months-range').val(months).change();
            }
        });
    });

}
