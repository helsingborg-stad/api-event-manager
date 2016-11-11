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

    $('.acf-field[data-name="main_organizer"] input[type="checkbox"]').each(function(i, obj) {
        if ($(this).prop('checked')) {
            $('.acf-field[data-name="main_organizer"]').not($(this).closest('.acf-field[data-name="main_organizer"]')).addClass('main_organizer_hidden');
        }
    });

    $('.acf-field[data-name="main_organizer"] input[type="checkbox"]').live('click', function () {
        if ($(this).prop('checked')) {
            $('.acf-field[data-name="main_organizer"]').not($(this).closest('.acf-field[data-name="main_organizer"]')).addClass('main_organizer_hidden');
        } else {
            $('.acf-field[data-name="main_organizer"]').removeClass('main_organizer_hidden');
        }
    });
});
