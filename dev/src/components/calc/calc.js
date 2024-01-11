export default () => {

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

        const payment = Math.round((amount + Math.round(amount*(rate*30/100)*months))/months);
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
