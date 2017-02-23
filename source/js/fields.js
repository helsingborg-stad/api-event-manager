ImportEvents = ImportEvents || {};
ImportEvents.Admin = ImportEvents.Admin || {};

ImportEvents.Admin.Fields = (function ($) {

    function Fields() {
        $(document).ready(function () {
            this.syncCheckBox();
            this.mainOrganizerCheckBox();
            this.locationGmaps();

            // Remove .button-primary from acf-buttons
            $('.acf-button').removeClass('button-primary');
            $('.acf-gallery-add').text(eventmanager.add_images);
        }.bind(this));
    }

    /**
     * Toggle sync event, adds "blocking" elements to fields to prevent them beeing edited
     * if sync is activated
     * @return {void}
     */
    Fields.prototype.syncCheckBox = function() {
        $('#sync-meta-box input[type="checkbox"]').on('change', function () {
            if ($('#sync-meta-box input[type="checkbox"]').is(':checked')) {
                $('body').addClass('no-sync');
                $(this).parent().addClass('check_active');
                return;
            }

            $('body').removeClass('no-sync');
            $(this).parent().removeClass('check_active');
        }).trigger('change');
    };

    /**
     * Hides "main organizer" checkbox for other orngaizer rows than the one that's checked
     * @return {void}
     */
    Fields.prototype.mainOrganizerCheckBox = function() {
        $('.acf-field[data-name="main_organizer"] input[type="checkbox"]').each(function(i, obj) {
            if ($(this).prop('checked')) {
                $('.acf-field[data-name="main_organizer"]').not($(this).closest('.acf-field[data-name="main_organizer"]')).addClass('main_organizer_hidden');
            }
        });

        $(document).on('click', '.acf-field[data-name="main_organizer"] input[type="checkbox"]', function () {
            if ($(this).prop('checked')) {
                $('.acf-field[data-name="main_organizer"]').not($(this).closest('.acf-field[data-name="main_organizer"]')).addClass('main_organizer_hidden');
            } else {
                $('.acf-field[data-name="main_organizer"]').removeClass('main_organizer_hidden');
            }
        });
    };

    /**
     * Hide Google map on post type location if address data is missing
     * @return {void}
     */
    Fields.prototype.locationGmaps = function() {
        $('.acf-field[data-name="geo_map"] .acf-hidden').each(function(i, obj) {
            var address = $(this).find('.input-address').attr('value');
            var lat = $(this).find('.input-lat').attr('value');
            var lng = $(this).find('.input-lng').attr('value');

            if (!address || !lat || !lng) {
                $('.acf-field[data-name="geo_map"]').hide();
            }
        });
    };

    return new Fields();

})(jQuery);
