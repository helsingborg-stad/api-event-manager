jQuery(document).ready(function ($) {

    $('.acf-field[data-name="sync"] input[type="checkbox"]').on('change', function () {
        if ($('.acf-field[data-name="sync"] input[type="checkbox"]').is(':checked')) {
            $('body').addClass('no-sync');
            $(this).parent().addClass('check_active');
        } else {
            $('body').removeClass('no-sync');
            $(this).parent().removeClass('check_active');
        }
    }).trigger('change');
});
