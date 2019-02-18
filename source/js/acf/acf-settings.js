(function($) {
    // Datepicker translations
    acf.add_filter('date_time_picker_args', function(args, $field) {
        args.timeOnlyTitle = eventmanager.choose_time;
        args.timeText = eventmanager.time;
        args.hourText = eventmanager.hour;
        args.minuteText = eventmanager.minute;
        args.closeText = eventmanager.done;
        args.currentText = eventmanager.now;
        args.showSecond = false;
        return args;
    });

    acf.add_filter('time_picker_args', function(args, $field) {
        args.timeOnlyTitle = eventmanager.choose_time;
        args.timeText = eventmanager.time;
        args.hourText = eventmanager.hour;
        args.minuteText = eventmanager.minute;
        args.closeText = eventmanager.done;
        args.currentText = eventmanager.now;
        args.showSecond = false;
        return args;
    });

    acf.add_filter('google_map_marker_args', function(args, $field) {
        args.draggable = false;
        args.raiseOnDrag = false;
        return args;
    });

    // Show validation errors on tabs
    acf.addFilter('validation_complete', function(args, field) {
        $('.acf-tab-error', field).remove();

        if (args.errors) {
            for (var i = 0; i < args.errors.length; i++) {
                var errorField = $('[name="' + args.errors[i].input + '"]', field).parents(
                    '.acf-field'
                );
                errorField = errorField[errorField.length - 1];
                var tab = $(errorField, field)
                    .prevAll('.acf-field-tab')
                    .attr('data-key');

                $('.acf-tab-wrap a[data-key=' + tab + '] .acf-tab-error', field).remove();
                $('.acf-tab-wrap a[data-key=' + tab + ']', field)
                    .append(' <span class="dashicons dashicons-warning acf-tab-error"></span>')
                    .click();
            }
        }

        return args;
    });
})(jQuery);
