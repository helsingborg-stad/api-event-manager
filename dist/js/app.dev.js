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

    /**
     * Limits datepickers for endtime and door time according to the starttime
     * @return {void}
     */
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

    /**
     * Show recurring rules exeptions in date picker
     * @return {void}
     */
    Fields.prototype.eventDateExceptions = function() {
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
    Fields.prototype.getClosestDay = function(date, dayOfWeek) {
        var resultDate = new Date(date.getTime());
        resultDate.setDate(date.getDate() + (7 + dayOfWeek - date.getDay()) % 7);

        return resultDate;
    };

    /**
     * Formats date to yyyy-mm-dd
     * @param  {Date} date
     * @return {string}
     */
    Fields.prototype.formattedDate = function(date) {
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
    Fields.prototype.weekdayNumber = function(weekdayString) {
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

    function Guide() {
        this.locationPicker();
    }

    Guide.prototype.locationPicker = function() {
        jQuery(function($){
            $(document).on('change','#acf-field_589498b7fc7b3-input', function() {

                var postId = this.getParameterByName('post');

                var data = {
                    'action': 'update_guide_sublocation_option',
                    'selected': $('#acf-field_589498b7fc7b3-input').val(),
                    'post_id': postId
                };

                if(postId) {
                    $.post(ajaxurl, data, function(response) {

                        var jsonResult = $.parseJSON(response);

                        $("[data-name='guide_object_location'] select").each(function(index, object){
                            var select = $(this);

                            if(select.length) {
                                var prePopulateSelected = select.val();

                                //Empty select
                                select.empty();

                                //Populate from json
                                $.each(jsonResult, function(i, item) {
                                    select.append($('<option/>', {
                                        value: i,
                                        text: item
                                    }).bind(item));
                                }.bind(select));

                                //Select previusly selected option
                                select.val(prePopulateSelected);

                            }
                        });

                    }.bind(this));
                } else {
                    alert("Please save the guide to allow sublocations to be selected.");
                }
            }.bind(this));
        }.bind(this));
    };

    Guide.prototype.getParameterByName = function(name) {
        var results = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(window.location.href);
        if (!results) {
            return undefined;
        }
        return results[1] || undefined;
    };

    //return new Guide();

})(jQuery);

var ImportEvents = ImportEvents || {};

ImportEvents = ImportEvents || {};
ImportEvents.Parser = ImportEvents.Parser || {};

ImportEvents.Parser.Eventhandling = (function ($) {

    var newPosts = {
        events: 0,
        locations: 0,
        contacts: 0
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
                        this.restoreButton(button, storedCss);
                    });
                }.bind(this));
            }
        }.bind(this));

        this.importModal();
    }

    Eventhandling.prototype.importModal = function() {
        if (!['edit-event', 'edit-location'].indexOf(pagenow)) {
            return;
        }

        $(document).ready(function () {
            $('#wpwrap').append('<div id="blackOverlay"></div>');
            $('.wrap').append('\
                <div id="importResponse">\
                    <div><h3>'+ eventmanager.new_data_imported +'</h3></div>\
                    <div class="inline"><p><strong>'+ eventmanager.events +'</strong></p></div><div class="inline"><p><strong>'+ eventmanager.locations +'</strong></p></div><div class="inline"><p><strong>'+ eventmanager.contacts +'</strong></p></div>\
                    <div class="inline"><p id="event">0</p></div><div class="inline"><p id="location">0</p></div><div class="inline"><p id="contact">0</p></div>\
                    <div id="untilReload"><div id="meter"></div><p>'+ eventmanager.time_until_reload +'</p></div>\
                </div>\
            ');
        })
    };

    /**
     * Parse CBIS & XCAP events, loop through each API key
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
                newPosts.events    += response.events;
                newPosts.locations += response.locations;
                newPosts.contacts  += response.contacts;

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
                newPosts.events    += response.events;
                newPosts.locations += response.locations;
                newPosts.contacts  += response.contacts;
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
            var eventNumber = responsePopup.find('#event');
            var locationNumber = responsePopup.find('#location');
            var contactNumber = responsePopup.find('#contact');
            var normalTextSize = eventNumber.css('fontSize');
            var bigTextSize = '26px';

            eventNumber.text(newData.events);
            locationNumber.text(newData.locations);
            contactNumber.text(newData.contacts);

            eventNumber.animate({opacity: 1}, long).animate({fontSize: bigTextSize}, short).animate({fontSize: normalTextSize}, short, function() {
                locationNumber.animate({opacity: 1}, long).animate({fontSize: bigTextSize}, short).animate({fontSize: normalTextSize}, short, function() {
                    contactNumber.animate({opacity: 1}, long).animate({fontSize: bigTextSize}, short).animate({fontSize: normalTextSize}, short, function() {
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

ImportEvents.Admin.Suggestions = (function ($) {

    var typingTimer;
    var lastTerm;

    var acceptedPagenow = [
        'contact',
        'location',
        'event',
        'sponsor',
        'package',
        'membership-card'
    ];

    function Suggestions() {
        if (acceptedPagenow.indexOf(pagenow) < 0) {
            return;
        }

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

        $suggestions.find('ul').append('<li><strong>' + eventmanager.similar_posts + ':</strong> <button type="button" class="notice-dismiss suggestion-hide" data-action="suggestions-close"></button></li>');

        $.each(suggestions, function (index, suggestion) {
            var title = pagenow === 'event' ? suggestion.title : suggestion.title.rendered;
            var pageText = title.replace("<span>","").replace("</span>"),
            regex = new RegExp("(" + term + ")", "igm"),
            highlighted = pageText.replace(regex ,"<span>$1</span>");

            $suggestions.find('ul').append('<li><a href="' + eventmanager.adminurl + ' /post.php?post=' + suggestion.id + '&action=edit" class="suggestion">' + highlighted + '</a></li>');
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

    return new Suggestions();

})(jQuery);

ImportEvents = ImportEvents || {};
ImportEvents.Admin = ImportEvents.Admin || {};

ImportEvents.Admin.Validate = (function ($) {
    function Validate() {
        $(document).on('click', '#publish', function (e) {
            return this.post_title(e);
        }.bind(this));

        $(document).on('keypress', 'input[name="post_title"]', function (e) {
            if (e.which != 13) {
                return true;
            }

            return this.post_title(e);
        }.bind(this));
    }

    Validate.prototype.post_title = function(e) {
        var $title = $('#title');

        if ($title.val().length > 0) {
            $('.require-post').remove();
            return true;
        }

        setTimeout(function () {
            $('#ajax-loading').css('visibility', 'hidden');
        }, 100);

        setTimeout(function () {
            $('#publish').removeClass('button-primary-disabled');
        }, 100);

        if (!$(".require-post").length) {
            $('#post').before('<div class="error require-post"><p>' + eventmanager.require_title + '</p></div>');
        }

        return false;
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
            this.createTrigger('contact', '.acf-field-57ebb807988f8');
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
               $(triggerClass).append('<a class="createNewPost button" href="//' + window.location.host + '/wp/wp-admin/post-new.php?post_type=' + postType+ '&lightbox=true">' + eventmanager['new_' + postType] + '</a>');
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
        $('.notice.is-dismissible').on('click', '.notice-dismiss', function(event){
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
