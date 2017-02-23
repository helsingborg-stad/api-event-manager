ImportEvents = ImportEvents || {};
ImportEvents.Admin = ImportEvents.Admin || {};

ImportEvents.Admin.Fields = (function ($) {

    function Fields() {
        $(document).ready(function () {
            this.syncCheckBox();
            this.mainOrganizerCheckBox();
            this.locationGmaps();
            this.eventDatepickerRange();

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

    Fields.prototype.eventDatepickerRange = function() {
        $(document).on('click', '.acf-field-576110e583969 .hasDatepicker, .acf-field-5761169e07309 .hasDatepicker', function () {
            var date = $(this).parents('.acf-fields').find('[data-name="start_date"] .hasDatepicker').val();

            if (!date) {
                return;
            }

            var d = date.split(/[ ]+/);
            var r = d[0].split(/[-]+/);
            var m = r[1] - 1;

            date = new Date(r[0], m, r[2], 0, 0, 0);

            if (Object.prototype.toString.call(date) === "[object Date]" ) {
                if (!isNaN(date.getTime())) {
                    var year = date.getFullYear();
                    var month = date.getMonth();
                    var day = date.getDate();

                    if ($(this).parents('[data-name="end_date"]').length) {
                        var end_date = new Date(year + 1, month, day);

                        $(this).datetimepicker("option", "minDate", date);
                        $(this).datetimepicker("option", "maxDate", end_date);
                    }

                    if ($(this).parents('[data-name="door_time"]').length) {
                        var start_date = new Date(year - 1, month, day);

                        $(this).datetimepicker("option", "minDate", start_date);
                        $(this).datetimepicker("option", "maxDate", date);
                    }

                    $(this).datepicker("option", "defaultDate", date);
                    $(this).datepicker({showOn:'focus'}).focus();
                }
            }
        });
    };

    return new Fields();

})(jQuery);
