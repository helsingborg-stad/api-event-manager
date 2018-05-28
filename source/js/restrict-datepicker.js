ImportEvents = ImportEvents || {};
ImportEvents.Admin = ImportEvents.Admin || {};

ImportEvents.Admin.RestrictDatePicker = (function ($) {

    function RestrictDatePicker() {
        if (pagenow !== 'edit-event') {
            return;
        }

        this.init();
    }

    /**
     * Init datepicker
     * @return void
     */
    RestrictDatePicker.prototype.init = function () {
        $(document).ready(function () {
            var from = $('input[name="restrictDateFrom"]'),
                to = $('input[name="restrictDateTo"]');

            from.datepicker({dateFormat : "dd-mm-yy"});
            to.datepicker({dateFormat : "dd-mm-yy"});

            from.on('change', function () {
                to.datepicker('option', 'minDate', from.val());
            });

            to.on('change', function () {
                from.datepicker('option', 'maxDate', to.val());
            });
        });
    };

    return new RestrictDatePicker();

})(jQuery);
