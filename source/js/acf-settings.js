// ACF date picker settings
(function($) {
    if (typeof acf != 'undefined') {
        // Datepicker translations
        acf.add_filter('date_time_picker_args', function( args, $field ){
            args.timeOnlyTitle = eventmanager.choose_time;
            args.timeText = eventmanager.time;
            args.hourText = eventmanager.hour;
            args.minuteText = eventmanager.minute;
            args.closeText = eventmanager.done;
            args.currentText = eventmanager.now;
            args.showSecond = false;
            return args;
        });
        acf.add_filter('time_picker_args', function( args, $field ){
            args.timeOnlyTitle = eventmanager.choose_time;
            args.timeText = eventmanager.time;
            args.hourText = eventmanager.hour;
            args.minuteText = eventmanager.minute;
            args.closeText = eventmanager.done;
            args.currentText = eventmanager.now;
            args.showSecond = false;
            return args;
        });

        acf.add_filter('google_map_marker_args', function( args, $field ){
            args.draggable = false;
            args.raiseOnDrag = false;
            return args;
        });

        // Show validation errors on tabs
        acf.add_filter('validation_complete', function( json, $form ){
            $('.acf-tab-error', $form).remove();
            if(json.errors) {
                for (var i = 0; i < json.errors.length; i++) {
                    var field = $('[name="' + json.errors[i].input + '"]', $form).parents('.acf-field');
                        field = field[field.length - 1];
                    var tab = $(field, $form).prevAll('.acf-field-tab').attr('data-key');
                    $('.acf-tab-wrap a[data-key=' + tab + '] .acf-tab-error', $form).remove();
                    $('.acf-tab-wrap a[data-key=' + tab + ']', $form).append(' <span class="dashicons dashicons-warning acf-tab-error"></span>').click();
                }
            }
            return json;
        });
    }
})(jQuery);
