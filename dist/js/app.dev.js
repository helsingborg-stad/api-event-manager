ImportEvents = ImportEvents || {};
ImportEvents.Admin = ImportEvents.Admin || {};

ImportEvents.Admin.AcceptDeny = (function ($) {

    function AcceptDeny() {
        $(function(){
            this.handleEvents();
        }.bind(this));
    }

    /**
     * Accept or deny events.
     * @param  int postStatus 1 = accept, 0 = deny
     * @param  int postId     event object id
     * @return void
     */
    AcceptDeny.prototype.changeAccepted = function(postStatus, postId) {
        $.ajax({
            url: eventmanager.ajaxurl,
            type: 'post',
            data: {
                action    : 'accept_or_deny',
                value     : postStatus,
                postId    : postId
            },
            beforeSend: function(response) {
                var postElement = $('#post-' + postId);

                if (postStatus === 1) {
                    postElement.find('.deny').removeClass('hidden');
                    postElement.find('.accept').addClass('hidden');
                } else if(postStatus === 0) {
                    postElement.find('.deny').addClass('hidden');
                    postElement.find('.accept').removeClass('hidden');
                }
            }
        });
    };

    /**
     * Handle events
     * @return void
     */
    AcceptDeny.prototype.handleEvents = function () {
        $(document).on('click', '.accept', function (e) {
            e.preventDefault();
            var postId = $(e.target).closest('.accept').attr('post-id');
            this.changeAccepted(1, postId);
        }.bind(this));

        $(document).on('click', '.deny', function (e) {
            e.preventDefault();
            var postId = $(e.target).closest('.deny').attr('post-id');
            this.changeAccepted(0, postId);
        }.bind(this));
    };

    return new AcceptDeny();

})(jQuery);

// ACF date picker settings
(function($) {
    if (typeof acf !== 'undefined') {
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

            if (json.errors) {
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

ImportEvents = ImportEvents || {};
ImportEvents.Admin = ImportEvents.Admin || {};

ImportEvents.Admin.Fields = (function ($) {

    function Fields() {
        $(document).ready(function () {
            this.syncCheckBox();
            this.mainOrganizerCheckBox();
            this.locationGmaps();
            this.eventDatepickerRange();
            this.eventDateExceptions();
            this.duplicateOccasion();

            // Remove .button-primary from acf-buttons
            $('.acf-button').removeClass('button-primary');
            $('.acf-gallery-add').text(eventmanager.add_images);
        }.bind(this));
    }

    /**
     * Adds a button to duplicate occasions
     */
    Fields.prototype.duplicateOccasion = function () {
        $('.acf-field-57611277ef032').each(function () {
            $(this).after('<div class="acf-field"><a href="#" class="duplicateOccasion button">' + eventmanager.duplicate_occasion + '</a></div>');
        });

        $(document).on('click', '.duplicateOccasion', function (e) {
            e.preventDefault();
            $('a[data-event="add-row"]', '.acf-field-5761106783967').last().trigger('click');

            var $target = $(e.target).closest('.acf-row'),
                startDate = $('div[data-name="start_date"] .input-alt', $target).val(),
                endDate = $('div[data-name="end_date"] .input-alt', $target).val(),
                doorTime = $('div[data-name="door_time"] .input-alt', $target).val(),
                status = $('div[data-name="status"] input:checked', $target).val(),
                exceptionInfo = $('div[data-name="occ_exeption_information"] input', $target).val(),
                contentMode = $('div[data-name="content_mode"] input:checked', $target).val(),
                tinyMceId = $('textarea[class="wp-editor-area"]', $target).attr('id'),
                tinyeMceContent = '';

            if (typeof tinyMCE !== 'undefined') {
                tinyeMceContent = tinymce.get(tinyMceId).getContent();
            }

            // Update the new occasion with cloned values
            setTimeout(function () {
                var $clonedRow = $('[data-name="occasions"] table tbody').children('tr.acf-row:not(.acf-clone)').last(),
                    newTinyMceId = $('textarea[class="wp-editor-area"]', $clonedRow).attr('id');

                $('div[data-name="start_date"] .hasDatepicker', $clonedRow).datetimepicker('setDate', startDate);
                $('div[data-name="end_date"] .hasDatepicker', $clonedRow).datetimepicker('setDate', endDate);
                $('div[data-name="door_time"] .hasDatepicker', $clonedRow).datetimepicker('setDate', doorTime);
                $('input[value="' + status + '"]', $clonedRow).attr('checked', 'checked').trigger('change');
                $('div[data-name="occ_exeption_information"] input', $clonedRow).val(exceptionInfo);
                $('input[value="' + contentMode + '"]', $clonedRow).attr('checked', 'checked').trigger('change');

                if (typeof tinyMCE !== 'undefined') {
                    tinymce.get(newTinyMceId).setContent(tinyeMceContent);
                }
            }, 0);
        });
    };

    /**
     * Toggle sync event, adds "blocking" elements to fields to prevent them beeing edited
     * if sync is activated
     * @return {void}
     */
    Fields.prototype.syncCheckBox = function () {
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
    Fields.prototype.mainOrganizerCheckBox = function () {
        $('.acf-field[data-name="main_organizer"] input[type="checkbox"]').each(function (i, obj) {
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
    Fields.prototype.locationGmaps = function () {
        $('.acf-field[data-name="geo_map"] .acf-hidden').each(function (i, obj) {
            var address = $(this).find('.input-address').attr('value');
            var lat = $(this).find('.input-lat').attr('value');
            var lng = $(this).find('.input-lng').attr('value');

            if (!address || !lat || !lng) {
                $('.acf-field[data-name="geo_map"]').hide();
            }
        });
    };

    /**
     * Limits datepickers for endtime and door time according to the starttime
     * @return {void}
     */
    Fields.prototype.eventDatepickerRange = function () {
        $(document).on('click', '.acf-field-576110e583969 .hasDatepicker, .acf-field-5761169e07309 .hasDatepicker', function () {

            var date = $(this).parentsUntil('.acf-fields').siblings('.start_date').find('.hasDatepicker').val();

            if (!date) {
                return;
            }

            var d = date.split(/[ ]+/);
            var r = d[0].split(/[-]+/);
            var m = r[1] - 1;

            date = new Date(r[0], m, r[2], 0, 0, 0);

            if (Object.prototype.toString.call(date) === "[object Date]") {
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
                    $(this).datepicker({showOn: 'focus'}).focus();
                }
            }
        });
    };

    /**
     * Show recurring rules exeptions in date picker
     * @return {void}
     */
    Fields.prototype.eventDateExceptions = function () {
        $(document).on('click', '.acf-field-57d279f8db0cc .hasDatepicker', function (e) {
            $this = $(e.target).closest('.hasDatepicker');

            $this.datepicker('option', 'dateFormat', 'yy-mm-dd');

            var weekDay = $this.parents('.acf-field-repeater').siblings('[data-name="rcr_week_day"]').find('select').val();
            var startDate = $this.parents('.acf-field-repeater').siblings('[data-name="rcr_start_date"]').find('.hasDatepicker').val();
            var endDate = $this.parents('.acf-field-repeater').siblings('[data-name="rcr_end_date"]').find('.hasDatepicker').val();

            $this.datepicker('option', 'defaultDate', startDate);

            if (startDate && endDate) {
                var start = this.getClosestDay(new Date(startDate), this.weekdayNumber(weekDay));
                var end = new Date(endDate);

                var occurances = [];

                for (var dat = new Date(start); dat <= end; dat.setDate(dat.getDate() + 7)) {
                    occurances.push(this.formattedDate(new Date(dat)));
                }

                var disableSepcificDate = function (date) {
                    var string = jQuery.datepicker.formatDate('yy-mm-dd', date);
                    return [occurances.indexOf(string) != -1];
                };

                $this.datepicker('option', 'beforeShowDay', disableSepcificDate);
            }

            $this.datepicker({showOn: 'focus'}).focus();
        }.bind(this));
    };

    /**
     * Gets the closest date that matches a day of week
     * @param  {Date} date        Date object
     * @param  {[type]} dayOfWeek [description]
     * @return {[type]}           [description]
     */
    Fields.prototype.getClosestDay = function (date, dayOfWeek) {
        var resultDate = new Date(date.getTime());
        resultDate.setDate(date.getDate() + (7 + dayOfWeek - date.getDay()) % 7);

        return resultDate;
    };

    /**
     * Formats date to yyyy-mm-dd
     * @param  {Date} date
     * @return {string}
     */
    Fields.prototype.formattedDate = function (date) {
        var curr_date = ('0' + date.getDate()).slice(-2);
        var curr_month = ('0' + (date.getMonth() + 1)).slice(-2);
        var curr_year = date.getFullYear();

        return curr_year + "-" + curr_month + "-" + curr_date;
    };

    /**
     * Converts weekday in string to weekday number
     * @param  {string} weekdayString Weekday as string
     * @return {int}                  Weekday as int
     */
    Fields.prototype.weekdayNumber = function (weekdayString) {
        var weekday = [];

        weekday.monday = 1;
        weekday.tuesday = 2;
        weekday.wednesday = 3;
        weekday.thursday = 4;
        weekday.friday = 5;
        weekday.saturday = 6;
        weekday.sunday = 7;

        if (typeof weekday[weekdayString.toLowerCase()] !== 'undefined') {
            return weekday[weekdayString.toLowerCase()];
        }

        return 0;
    };

    return new Fields();

})(jQuery);

ImportEvents = ImportEvents || {};
ImportEvents.Admin = ImportEvents.Admin || {};

ImportEvents.Admin.Guide = (function ($) {

    var select2_args_internal = {};

    function Guide() {
        $(document).ready(function () {

            if (pagenow === 'guide') {
                this.onlySublocations();

                // Populate objects when clicking the beacon tab
                $(document).on('click', '.acf-tab-button[data-key="field_58ab0c6354b09"]', function () {
                    this.populateBeaconObjects();
                }.bind(this));

                // Populate objects when clicking the "add new row" button in beacon tab
                $(document).on('click', '[data-name="guide_beacon"] [data-event="add-row"]', function () {
                    setTimeout(function () {
                        var $row = $('[data-name="guide_beacon"] .acf-row:not(.acf-clone)').last();
                        var $input = $row.find('[data-name="objects"] .acf-input input');

                        $input.select2('destroy');
                        $input.select2({
                            data: this.getObjects(),
                            multiple: true
                        });
                    }.bind(this), 1);
                }.bind(this));
            }

        }.bind(this));
    }

    /**
     * Only use sublocations in locations selector
     * @return {void}
     */
    Guide.prototype.onlySublocations = function() {
        acf.add_filter('select2_ajax_data', function( data, args, $input, $field ){
            if (data.field_key !== 'field_58ab0c9554b0a') {
                return data;
            }

            var groupInputId = 'acf-field_589dd138aca7e-input';
            var selectedGroup = $('#' + groupInputId).val();

            if (selectedGroup) {
                data.selectedGroup = selectedGroup;
            }

            // return
            return data;
        });
    };

    /**
     * Populate objects selectors
     * @return {void}
     */
    Guide.prototype.populateBeaconObjects = function() {
        $('[data-name="objects"] select ~ input[type="hidden"]').each(function (index, element) {
            $(element).select2('destroy');
            $(element).select2({
                data: this.getObjects(),
                multiple: true
            });
        }.bind(this));
    };

    /**
     * Get objects from the Content Objects tab to fill the objects selector with
     * @return {array}
     */
    Guide.prototype.getObjects = function() {
        var objects = [];

        $('[data-name="guide_content_objects"] table tbody').first().children('tr.acf-row:not(.acf-clone)').each(function (index, item) {
            var $item = $(item);

            var uid = $item.find('[data-name="guide_object_uid"] input').val();
            var title = $item.find('[data-name="guide_object_title"] input').val();

            if (!uid) {
                uid = new Date().valueOf();
                $item.find('[data-name="guide_object_uid"] input').val(uid);
            }

            objects.push({
                id: uid,
                text: title
            });
        });

        return objects;
    };

    return new Guide();

})(jQuery);

var ImportEvents = ImportEvents || {};

ImportEvents = ImportEvents || {};
ImportEvents.Parser = ImportEvents.Parser || {};

ImportEvents.Parser.Eventhandling = (function ($) {

    var newPosts = {
        events: 0,
        locations: 0,
        organizers: 0
    };

    var data = {
        action: 'import_events',
        value: '',
        api_keys: '',
        cron: false
    };

    var short = 200;
    var long = 400;
    var timerId = null;
    var loadingOccasions = false;
    var i = 0;
    var j = 0;

    function Eventhandling() {
        $(document).on('click', '#transticket', function (e) {
            e.preventDefault();
            data.value = 'transticket';

            if (!loadingOccasions) {
                loadingOccasions = true;

                var button = $(e.target).closest('#transticket');
                var storedCss = this.collectCssFromButton(button);

                this.redLoadingButton(button, function() {
                    this.parseEvents(data, button, storedCss);
                    return;
                }.bind(this));
            }
        }.bind(this));

        $(document).on('click', '#xcap', function (e) {
            e.preventDefault();
            data.value = 'xcap';

            if (!loadingOccasions) {
                loadingOccasions = true;

                var button = $(e.target).closest('#xcap');
                var storedCss = this.collectCssFromButton(button);

                this.redLoadingButton(button, function() {
                    this.parseEvents(data, button, storedCss);
                    return;
                }.bind(this));
            }
        }.bind(this));

        $(document).on('click', '#cbis', function (e) {
            e.preventDefault();
            data.value = 'cbis';

            if (!loadingOccasions) {
                loadingOccasions = true;

                var button = $(e.target).closest('#cbis');
                var storedCss = this.collectCssFromButton(button);

                this.redLoadingButton(button, function() {
                    this.parseEvents(data, button, storedCss);
                    return;
                }.bind(this));
            }
        }.bind(this));

        $(document).on('click', '#cbislocation', function (e) {
            e.preventDefault();
            data.value = 'cbislocation';

            if (!loadingOccasions) {
                loadingOccasions = true;

                var button = $(e.target).closest('#cbislocation');
                var storedCss = this.collectCssFromButton(button);

                this.redLoadingButton(button, function() {
                    this.parseCbislocation(data, button, storedCss);
                    return;
                }.bind(this));
            }
        }.bind(this));

        $(document).on('click', '#arcgislocation', function (e) {
            e.preventDefault();
            data.value = 'arcgislocation';

            if (!loadingOccasions) {
                loadingOccasions = true;

                var button = $(e.target).closest('#arcgislocation');
                var storedCss = this.collectCssFromButton(button);

                this.redLoadingButton(button, function() {
                    this.parseEvents(data, button, storedCss);
                    return;
                }.bind(this));
            }
        }.bind(this));

        $(document).on('click', '#occasions', function (e) {
            e.preventDefault();

            if (!loadingOccasions) {
                loadingOccasions = true;

                var button = $(e.target).closest('#occasions');
                var storedCss = this.collectCssFromButton(button);

                this.redLoadingButton(button, function() {
                    var data = {
                        action: 'collect_occasions'
                    };

                    jQuery.post(ajaxurl, data, function(response) {
                        loadingOccasions = false;
                        Eventhandling.prototype.restoreButton(button, storedCss);
                    });
                }.bind(this));
            }
        }.bind(this));

        this.importModal();
    }

    Eventhandling.prototype.importModal = function() {
        if (['edit-event', 'edit-location'].indexOf(pagenow) == -1) {
            return;
        }

        $(document).ready(function () {
            $('#wpwrap').append('<div id="blackOverlay"></div>');
            $('.wrap').append('\
                <div id="importResponse">\
                    <div><h3>'+ eventmanager.new_data_imported +'</h3></div>\
                    <div class="inline"><p><strong>'+ eventmanager.events +'</strong></p></div><div class="inline"><p><strong>'+ eventmanager.locations +'</strong></p></div><div class="inline"><p><strong>'+ eventmanager.organizers +'</strong></p></div>\
                    <div class="inline"><p id="event">0</p></div><div class="inline"><p id="location">0</p></div><div class="inline"><p id="organizer">0</p></div>\
                    <div id="untilReload"><div id="meter"></div><p>'+ eventmanager.time_until_reload +'</p></div>\
                </div>\
            ');
        })
    };

    /**
     * Parse CBIS, XCAP, TransTicket events, loop through each API key
     * @param  {array}   data        Data to parse
     * @param  {element} button      Clicked button
     * @param  {object}  storedCss   Default button  css
     * @return {void}
     */
    Eventhandling.prototype.parseEvents = function(data, button, storedCss) {
        if (data.value === 'cbis') {
            data.api_keys = cbis_ajax_vars.cbis_keys[i];
        } else if (data.value === 'xcap') {
            data.api_keys = xcap_ajax_vars.xcap_keys[i];
        } else if (data.value === 'transticket') {
            data.api_keys = transticket_ajax_vars.transticket_keys[i];
        } else if (data.value === 'arcgislocation') {
            data.api_keys = arcgis_ajax_vars.arcgis_keys[i];
        } else {
            return;
        }
        
        // Show result if there's no API keys left to parse
        if (typeof data.api_keys === 'undefined') {
            loadingOccasions = false;

            // Show data pop up if function is not called with cron
            if (!data.cron) {
                this.dataPopUp(newPosts);
                this.restoreButton(button, storedCss);
            }

            return;
        }

        $.ajax({
            url: eventmanager.ajaxurl,
            type: 'post',
            data: data,
            success: function(response) {
                // Update response object
                newPosts.events      += response.events;
                newPosts.locations   += response.locations;
                newPosts.organizers  += response.organizers;

                // Run function again
                i++;
                ImportEvents.Parser.Eventhandling.parseEvents(data, button, storedCss);
            }
        });
    };

    /**
     * Parse CBIS locations, loop through each API key and its categories
     * @param  {object}  data      Data to parse
     * @param  {element} button    Button element
     * @param  {object}  storedCss Button default css
     * @return {void}
     */
    Eventhandling.prototype.parseCbislocation = function(data, button, storedCss) {
        j = 0;

        // Show import result when done
        if( (typeof cbis_ajax_vars.cbis_keys[i] === 'undefined') ) {
            loadingOccasions = false;

            // Show data pop up if function is not called with cron
            if (!data.cron) {
                this.dataPopUp(newPosts);
                this.restoreButton(button, storedCss);
            }

            return;
        }

        data.api_keys = cbis_ajax_vars.cbis_keys[i];

        // Wait for callback and run this function again until there's no API keys left to parse
        $.when(this.parseLocations(data)).then(function() {
            i++;
            this.parseCbislocation(data, button, storedCss) ;
        }.bind(this));
    };

    /**
     * Parse each location category ID
     * @param  {object} data Data to parse
     * @return {object}      Deferred object
     */
    Eventhandling.prototype.parseLocations = function(data){
        var deferredObject = $.Deferred();

        Eventhandling.prototype.parse = function() {
            // Return when done
            if (typeof data.api_keys.cbis_locations[j] === 'undefined') {
                deferredObject.resolve();
                return;
            }

            data.cbis_location = data.api_keys.cbis_locations[j];

            // Wait for Ajax callback and run this function again until there's no categories left
            $.when(this.parseLocationCategory(data)).then(function() {
                j++;
                this.parse(data);
            }.bind(this));
        };

        this.parse();

        return deferredObject.promise();
    };

    /**
     * Call ajax with category ID
     * @param  {object} data Ajax data
     * @return {void}
     */
    Eventhandling.prototype.parseLocationCategory = function(data){
        return $.ajax({
            url: eventmanager.ajaxurl,
            type: 'post',
            data: data,
            success: function(response) {
                // Update response object
                newPosts.events      += response.events;
                newPosts.locations   += response.locations;
                newPosts.organizers  += response.organizers;
            }
        });
    };

    /**
     * Show data popup
     * @param  {object} newData Data to display
     * @return {void}
     */
    Eventhandling.prototype.dataPopUp = function(newData){
        $('#blackOverlay').show();
        var responsePopup = $('#importResponse');

        responsePopup.show(500, function() {
            var eventNumber     = responsePopup.find('#event');
            var locationNumber  = responsePopup.find('#location');
            var organizerNumber = responsePopup.find('#organizer');
            var normalTextSize  = eventNumber.css('fontSize');
            var bigTextSize     = '26px';

            eventNumber.text(newData.events);
            locationNumber.text(newData.locations);
            organizerNumber.text(newData.organizers);

            eventNumber.animate({opacity: 1}, long).animate({fontSize: bigTextSize}, short).animate({fontSize: normalTextSize}, short, function() {
                locationNumber.animate({opacity: 1}, long).animate({fontSize: bigTextSize}, short).animate({fontSize: normalTextSize}, short, function() {
                    organizerNumber.animate({opacity: 1}, long).animate({fontSize: bigTextSize}, short).animate({fontSize: normalTextSize}, short, function() {
                        var loadingBar = responsePopup.find('#untilReload #meter');
                        loadingBar.animate({width: '100%'}, 7000, function() {
                            location.reload();
                        });
                    });
                });
            });
        });
    };

    /**
     * Collects a object with css params for a button
     * @param  {element} button The button
     * @return {object}         The button style
     */
    Eventhandling.prototype.collectCssFromButton = function (button) {
        return {
            bgColor: button.css('background-color'),
            textColor: button.css('color'),
            borderColor: button.css('border-color'),
            textShadow: button.css('text-shadow'),
            boxShadow: button.css('box-shadow'),
            width: button.css('width'),
            text: button.text()
        };
    };

    /**
     * Transforms button style to red loading button
     * @param  {element}   button    The button to trasnform
     * @param  {Function}  callback  Callback function
     * @return {void}
     */
    Eventhandling.prototype.redLoadingButton = function (button, callback) {
        button.fadeOut(500, function() {
            var texts = [eventmanager.loading + '&nbsp;&nbsp;&nbsp;', eventmanager.loading + '.&nbsp;&nbsp;', eventmanager.loading + '..&nbsp;', eventmanager.loading + '...'];
            button.css('background-color', 'rgb(51, 197, 255)');
            button.css('border-color', 'rgb(0, 164, 230)');
            button.css('color', 'white');
            button.css('text-shadow', '0 -1px 1px rgb(0, 164, 230),1px 0 1px rgb(0, 164, 230),0 1px 1px rgb(0, 164, 230),-1px 0 1px rgb(0, 164, 230)');
            button.css('box-shadow', 'none');
            button.css('width', '85px');
            button.html(texts[0]);
            button.fadeIn(500);

            var counter = 1;
            timerId = setInterval(function() {
                if (counter > 3) {
                    counter = 0;
                }

                button.html(texts[counter]);
                ++counter;
            }, 500);

            if (callback !== undefined)
                callback();
        });
    };

    /**
     * Restores a button to its default state
     * @param  {element} button    The button
     * @param  {object}  storedCss The default css
     * @return {void}
     */
    Eventhandling.prototype.restoreButton = function (button, storedCss) {
        button.fadeOut(500, function() {
            button.css('background-color', storedCss.bgColor);
            button.css('color', storedCss.textColor);
            button.css('border-color', storedCss.borderColor);
            button.css('text-shadow', storedCss.textShadow);
            button.css('box-shadow', storedCss.boxShadow);
            button.css('width', storedCss.width);
            button.text(storedCss.text);
            button.fadeIn(500);

            clearTimeout(timerId);
        });
    };

    return new Eventhandling();

})(jQuery);

ImportEvents = ImportEvents || {};
ImportEvents.Admin = ImportEvents.Admin || {};

ImportEvents.Admin.MediaApprove = (function ($) {

    function MediaApprove() {
        $(function() {
            var $imageBox = $('div#postimagediv > .inside');

            if (pagenow !== 'event' || $imageBox.find('img').length > 0) {
                return;
            }

            this.addCheckBoxes($imageBox);
            this.handleEvents($imageBox);
        }.bind(this));
    }

    MediaApprove.prototype.addCheckBoxes = function(imageBox) {
        imageBox.children().hide();
        $('.acf-gallery-add').attr('disabled', true).prop('disabled', true);
        var checkBoxes =  '<div id="image-approve"><p><strong>' + eventmanager.confirm_statements + '</strong></p>';
            checkBoxes += '<input type="checkbox" name="approve" id="first-approve" value="1">';
            checkBoxes += '<span> ' + eventmanager.promote_event + '</span>';
            checkBoxes += '<p>' + eventmanager.identifiable_persons + '</p>';
            checkBoxes += '<p><input type="radio" name="approve" value="1">' + eventmanager.yes + ' <input type="radio" name="approve" value="0">' + eventmanager.no + '</p>';
            checkBoxes += '<div id="persons-approve" class="hidden"><input type="checkbox" name="approve" id="second-approve" value="1">';
            checkBoxes += '<span> ' + eventmanager.persons_approve + '</span></div></div>';
        imageBox.append(checkBoxes);
    };

    /**
     * Handle events
     * @return void
     */
    MediaApprove.prototype.handleEvents = function(imageBox) {
        $('input:radio[name=approve]').change(function() {
            if (this.value == 1) {
                $('#persons-approve').removeClass('hidden');
            } else {
                $('#persons-approve').addClass('hidden');
            }
        });

        $('input[name=approve]').change(function() {
            var firstCheck  = $('input:checkbox[id=first-approve]:checked').length > 0,
                radioCheck  = $('input:radio[name=approve]:checked').val(),
                secondCheck = $('input:checkbox[id=second-approve]:checked').length > 0;
            if (firstCheck && radioCheck == 0 || firstCheck && secondCheck) {
                $('#image-approve').remove();
                imageBox.find(':hidden').fadeIn();
                $('.acf-gallery-add').attr('disabled', false).prop('disabled', false);
            }
        });
    };

    return new MediaApprove();

})(jQuery);

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

ImportEvents = ImportEvents || {};
ImportEvents.Admin = ImportEvents.Admin || {};

ImportEvents.Admin.Suggestions = (function ($) {

    var typingTimer;
    var lastTerm;
    var suggestionString;

    var acceptedPagenow = [
        'organizer',
        'location',
        'event',
        'sponsor',
        'package',
        'membership-card',
        'guide'
    ];

    function Suggestions() {
        if (acceptedPagenow.indexOf(pagenow) < 0) {
            return;
        }

        this.switchName();

        $(document).on('keyup', 'input[name="post_title"]', function (e) {
            var $this = $(e.target);

            clearTimeout(typingTimer);

            typingTimer = setTimeout(function() {
                this.search($this.val());
            }.bind(this), 300);
        }.bind(this));

        $(document).on('click', '[data-action="suggestions-close"]', function (e) {
            e.preventDefault();
            this.dismiss();
        }.bind(this));
    }

    /**
     * Performs the search for similar titles
     * @param  {string} term Search term
     * @return {void}
     */
    Suggestions.prototype.search = function(term) {
        if (term.length <= 3 || term === lastTerm) {
            return false;
        }

        // Set last term to the current term
        lastTerm = term;

        // Get API endpoint for performning the search
        var geturl = eventmanager.wpapiurl + '/wp/v2/' + pagenow + '?search=' + term;

        if (pagenow === 'event') {
            geturl = eventmanager.wpapiurl + '/wp/v2/' + pagenow + '/search?term=' + term;
        }

        // Do the search request
        $.get(geturl, function(response) {
            if (!response.length) {
                this.dismiss();
                return;
            }

            this.output(response, term);
        }.bind(this), 'JSON');
    };

    /**
     * Outputs the title suggestions
     * @param  {array} suggestions
     * @param  {string} term
     * @return {void}
     */
    Suggestions.prototype.output = function(suggestions, term) {
        var $suggestions = $('#title-suggestions');

        if (!$suggestions.length) {
            $suggestions = $('<div id="title-suggestions"></div>');
            $suggestions.append('<ul></ul>');
        }

        $suggestions.find('ul').empty();

        $suggestions.find('ul').append('<li><strong>' + suggestionString + ':</strong> <button type="button" class="notice-dismiss suggestion-hide" data-action="suggestions-close"></button></li>');

        $.each(suggestions, function (index, suggestion) {
            var title = pagenow === 'event' ? suggestion.title : suggestion.title.rendered;
            var pageText = title.replace("<span>","").replace("</span>"),
            regex = new RegExp("(" + term + ")", "igm"),
            highlighted = pageText.replace(regex ,"<span>$1</span>");

            $suggestions.find('ul').append('<li><a href="' + eventmanager.adminurl + 'post.php?post=' + suggestion.id + '&action=edit" class="suggestion">' + highlighted + '</a></li>');
        });

        $('#titlewrap').append($suggestions);
        $suggestions.slideDown(200);
    };

    /**
     * Dismisses the suggestions
     * @return {void}
     */
    Suggestions.prototype.dismiss = function() {
        $('#title-suggestions').slideUp(200, function () {
            $('#title-suggestions').remove();
        });
    };

    Suggestions.prototype.switchName = function() {
        switch(pagenow) {
            case 'organizer':
                suggestionString = eventmanager.organizers + ' ' + eventmanager.with_similar_name;
                break;
            case 'location':
                suggestionString = eventmanager.locations + ' ' + eventmanager.with_similar_name;
                break;
            case 'sponsor':
                suggestionString = eventmanager.sponsors + ' ' + eventmanager.with_similar_name;
                break;
            case 'package':
                suggestionString = eventmanager.packages + ' ' + eventmanager.with_similar_name;
                break;
            case 'membership-card':
                suggestionString = eventmanager.membership_cards + ' ' + eventmanager.with_similar_name;
                break;
            case 'guide':
                suggestionString = eventmanager.guides + ' ' + eventmanager.with_similar_name;
                break;
            default:
                suggestionString = eventmanager.events + ' ' + eventmanager.with_similar_name;
        }
    };

    return new Suggestions();

})(jQuery);

ImportEvents = ImportEvents || {};
ImportEvents.Admin = ImportEvents.Admin || {};

ImportEvents.Admin.Validate = (function ($) {
    function Validate() {
        $(document).on('click', '#publish', function (e) {
            return this.post_title();
        }.bind(this));

        $(document).on('keypress', 'input[name="post_title"]', function (e) {
            if (e.which != 13) {
                return true;
            }

            return this.post_title();
        }.bind(this));
    }

    Validate.prototype.image = function() {
        var $imported = $('body').hasClass('imported');
        if (! $imported) {
            var $img = $('#postimagediv').find('img');
            if ($img.length > 0) {
                $('.require-image').remove();
            } else {
                var message = eventmanager.require_image
                this.showError('image', message);
                return false;
            }
        }

        return true;
    };

    Validate.prototype.post_title = function() {
        var $title = $('#title');
        if ($title.val().length > 0) {
            $('.require-title').remove();
        } else {
            var message = eventmanager.require_title
            this.showError('title', message);
            return false;
        }

        return true;
    };

    Validate.prototype.showError = function(field, message){
        setTimeout(function () {
            $('#ajax-loading').css('visibility', 'hidden');
        }, 100);

        setTimeout(function () {
            $('#publish').removeClass('button-primary-disabled');
        }, 100);

        if (!$(".require-" + field).length) {
            $('#post').before('<div class="error require-' + field + '"><p>' + message + '</p></div>');
        }
    };

    return new Validate();

})(jQuery);

ImportEvents = ImportEvents || {};
ImportEvents.Prompt = ImportEvents.Prompt || {};

ImportEvents.Prompt.Modal = (function ($) {

    var isOpen = false;

    function Modal() {
        $(function() {
            this.handleEvents();
        }.bind(this));
    }

    /**
     * Open modal
     * @param  {string} url      Url to open
     * @param  {} parentId
     * @return {void}
     */
    Modal.prototype.open = function (url, parentId) {
        $('body').addClass('lightbox-open').append('\
            <div id="lightbox">\
                <div class="lightbox-wrapper">\
                    <button class="lightbox-close" data-lightbox-action="close">&times; ' + eventmanager.close + '</button>\
                    <iframe class="lightbox-iframe" src="' + url + '" frameborder="0" allowtransparency></iframe>\
                </div>\
            </div>\
        ');

        if (typeof(parentId) != 'undefined') {
            $(".lightbox-iframe").bind("load", function() {
                var newContactForm = $(this).contents().find('#post');
                newContactForm.append('<input type="hidden" id="parentId" name="parentId" value="' + parentId + '" />');
            });
        }

        isOpen = true;
    };

    /**
     * Close modal
     * @return {void}
     */
    Modal.prototype.close = function () {
        var modalElement = $('.lightbox-iframe');

        $('body').removeClass('lightbox-open');
        $('#lightbox').remove();

        isOpen = false;
    };

    /**
     * Handle events
     * @return {void}
     */
    Modal.prototype.handleEvents = function () {
        $(document).on('click', '[data-lightbox-action="close"]', function (e) {
            e.preventDefault();
            this.close();
        }.bind(this));
    };

    return new Modal();

})(jQuery);

ImportEvents = ImportEvents || {};
ImportEvents.Admin = ImportEvents.Admin || {};

ImportEvents.Admin.NewPostModal = (function ($) {

    function NewPostModal() {
        $(function(){

            this.createTrigger('location', '.acf-field-576117c423a52');
            this.createTrigger('organizer', '.acf-field-5922a161ab32f');
            this.createTrigger('sponsor', '.acf-field-57a9d5f3804e1');
            this.createTrigger('membership-card', '.acf-field-57c7ed92054e6');
            this.createTrigger('membership-card', '.acf-field-581847f9642dc');

            this.bindLaunchModal();

        }.bind(this));
    }

    /**
     * Create button to trigger new post modal
     * @param  string   posttype to create
     * @param  string   triggering class or id
     * @return void
     */
    NewPostModal.prototype.createTrigger = function(postType, triggerClass) {
        if ($(triggerClass).length) {
            if (typeof eventmanager['new_' + postType] !== 'undefined') {
               $(triggerClass).append('<a class="createNewPost button" href="' + eventmanager.adminurl + 'post-new.php?post_type=' + postType+ '&lightbox=true">' + eventmanager['new_' + postType] + '</a>');
            }
        }
    };

    /**
     * Hook on trigger button to launch modal
     * @return void
     */
    NewPostModal.prototype.bindLaunchModal = function() {
        $(document).on('click','.createNewPost', function(e) {
            e.preventDefault();
            ImportEvents.Prompt.Modal.open($(this).attr('href'), $('#post_ID').val());
        });
    };

    return new NewPostModal();

})(jQuery);

ImportEvents = ImportEvents || {};
ImportEvents.Prompt = ImportEvents.Prompt || {};

ImportEvents.Prompt.Notice = (function ($) {

    function Notice() {
        $(document).on('click', '.event-guidelines .notice-dismiss', function (e) {
            this.dismissInstructions();
        }.bind(this));
    }

    Notice.prototype.dismissInstructions = function() {
        var data = {
            action: 'dismiss'
        };

        $.post(ajaxurl, data);
    };

    return new Notice();

})(jQuery);
