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

    $('.acf-button').removeClass('button-primary');

    // Hide Google map on post type location if address data is missing
    $('.acf-field[data-name="geo_map"] .acf-hidden').each(function(i, obj) {
        var address = $(this).find('.input-address').attr('value');
        var lat = $(this).find('.input-lat').attr('value');
        var lng = $(this).find('.input-lng').attr('value');
        if (!address || !lat || !lng) {
            $('.acf-field[data-name="geo_map"]').hide();
        }
    });

    $('.notice.is-dismissible').on('click', '.notice-dismiss', function(event){
        dismissInstructions();
    });

    $('.accept').click(function() {
        var postId = $(this).attr('postid');
        changeAccepted(1, postId);
    });

    $('.deny').click(function() {
        var postId = $(this).attr('postid');
        changeAccepted(-1, postId);
    });

    $('.acf-gallery-add').text(eventmanager.add_images);

    var oldInput = '';
    $('input[name="post_title"]').on('change paste keyup', function() {
        var input = $(this).val();

        if(input == oldInput)
            return;

        oldInput = input;
        if(input.length > 3)
        {
            var data = {
                'action'    : 'check_existing_title',
                'value'     : input,
                'postType'  : pagenow
            };
            var isevent = (pagenow === 'event') ? true : false;
            var geturl = (isevent) ? '/json/wp/v2/' + pagenow + '/search?term=' + input : '/json/wp/v2/' + pagenow + '?search=' + input;
            //jQuery.get('/json/wp/v2/' + pagenow + '?search=' + input, function(response) {
            jQuery.get(geturl, function(response) {
                $('#suggestionList').empty();
                for(var i in response) {
                    var id = response[i].id;
                    var title = (isevent) ? response[i].title : response[i].title.rendered;
                    var pageText = title.replace("<span>","").replace("</span>"),
                    regex = new RegExp("(" + input + ")", "igm"),
                    highlighted = pageText.replace(regex ,"<span>$1</span>");
                    $('#suggestionList').append('<li><a href="/wp/wp-admin/post.php?post=' + id + '&action=edit" class="suggestion">' + highlighted + '</a></li>');
                }
                if($('.suggestion').length == 0)
                    $('#suggestionContainer').fadeOut(200);
                else
                {
                    $('#suggestionList').prepend('<li><strong>' + eventmanager.similar_posts + ': <button class="notice-dismiss suggestion-hide" suggestion-hide-action="close"> </strong></li>');
                    $('#suggestionContainer').fadeIn(200);
                }
            });
        }
        else
            $('#suggestionContainer').fadeOut(200);
    });

    $(this).on('click', '[suggestion-hide-action="close"]', function(e) {
        e.preventDefault();
        $('#suggestionContainer').fadeOut(200);
    });

    if(pagenow == 'contact' || pagenow == 'location' || pagenow == 'event' || pagenow == 'sponsor' || pagenow == 'package' || pagenow == 'membership-card')
    {
        $('#titlewrap').after('<div id="suggestionContainer"><ul id="suggestionList"></ul></div>');
    }
    if(pagenow == 'edit-event' || pagenow == 'edit-location')
    {
        $('#wpwrap').append('<div id="blackOverlay"></div>');
        $('.wrap').append('\
            <div id="importResponse">\
                <div><h3>'+ eventmanager.new_data_imported +'</h3></div>\
                <div class="inline"><p><strong>'+ eventmanager.events +'</strong></p></div><div class="inline"><p><strong>'+ eventmanager.locations +'</strong></p></div><div class="inline"><p><strong>'+ eventmanager.contacts +'</strong></p></div>\
                <div class="inline"><p id="event">0</p></div><div class="inline"><p id="location">0</p></div><div class="inline"><p id="contact">0</p></div>\
                <div id="untilReload"><div id="meter"></div><p>'+ eventmanager.time_until_reload +'</p></div>\
            </div>\
        ');
    }

    // Set default end time value for occasion date picker
    $('body').on('click','.acf-field-576110e583969 .hasDatepicker', function() {
        var date = $(this).parents('.acf-field-576110e583969').prev().find('.hasDatepicker').val();
        if (date) {
            var d = date.split(/[ ]+/);
            var r = d[0].split(/[-]+/);
            var m = r[1] - 1;
            var date = new Date(r[0], m, r[2], 00, 00, 00);
            if ( Object.prototype.toString.call(date) === "[object Date]" ) {
                if (! isNaN(date.getTime() ) ) {
                var year = date.getFullYear();
                var month = date.getMonth();
                var day = date.getDate();
                var end_date = new Date(year + 1, month, day)
                $(this).datetimepicker( "option", "minDate", date);
                $(this).datetimepicker( "option", "maxDate", end_date);
                $(this).datepicker({showOn:'focus'}).focus();
                }
            }
        }
    });

    // Set default door time value for occasion date picker
    $('body').on('click','.acf-field-5761169e07309 .hasDatepicker', function() {
        var date = $(this).parents('.acf-field-5761169e07309').siblings('.acf-field-5761109a83968').find('.hasDatepicker').val();
        if (date) {
            var d = date.split(/[ ]+/);
            var r = d[0].split(/[-]+/);
            var m = r[1] - 1;
            var date = new Date(r[0], m, r[2], 00, 00, 00);
            if ( Object.prototype.toString.call(date) === "[object Date]" ) {
                if (! isNaN(date.getTime() ) ) {
                var year = date.getFullYear();
                var month = date.getMonth();
                var day = date.getDate();
                var start_date = new Date(year - 1, month, day)
                $(this).datetimepicker( "option", "minDate", start_date);
                $(this).datetimepicker( "option", "maxDate", date);
                $(this).datepicker( "option", "defaultDate", date);
                $(this).datepicker({showOn:'focus'}).focus();
                }
            }
        }
    });

    // Show recurring rules exeptions in date picker
    $('body').on('click','.acf-field-57d279f8db0cc .hasDatepicker', function() {
        $(this).datepicker( "option", "dateFormat", "yy-mm-dd" );

       var weekDay = $(this).parents('.acf-field-57d279addb0cb')
            .siblings('.acf-field-57d275713bf4e')
               .find(':selected').val();
        var startDate = $(this).parents('.acf-field-57d279addb0cb')
            .siblings('.acf-field-57d660a687234')
               .find('.hasDatepicker').val();
        var endDate = $(this).parents('.acf-field-57d279addb0cb')
            .siblings('.acf-field-57d2787b3bf51')
               .find('.hasDatepicker').val();

        $(this).datepicker( "option", "defaultDate", startDate );

        if (startDate && endDate) {
            var start = getClosestDay(new Date(startDate), convertDays(weekDay) );
            var end = new Date(endDate);
            var occurances = [];
            for (var dat = new Date(start); dat <= end; dat.setDate(dat.getDate() + 7)) {
                occurances.push(formattedDate(new Date(dat)));
            }
            function disableSpecificDates(date) {
                var string = jQuery.datepicker.formatDate('yy-mm-dd', date);
                return [occurances.indexOf(string) != -1];
            }
            $(this).datepicker( "option", "beforeShowDay", disableSpecificDates );
        }
        $(this).datepicker({showOn:'focus'}).focus();
    });

    // Require post title when publish posts
    $('#publish').click(function() {
        var testervar = jQuery('[id^="titlediv"]').find('#title');
        if (testervar.length && testervar.val().length < 1) {
            setTimeout("jQuery('#ajax-loading').css('visibility', 'hidden');", 100);
            if (!jQuery('.require-post').length) {
                jQuery('#post').before('<div class="error require-post"><p>' + eventmanager.require_title + '</p></div>');
            }
                setTimeout("jQuery('#publish').removeClass('button-primary-disabled');", 100);
                return false;
            } else {
                jQuery('.require-post').remove();
            }
    });
    $('#title').keypress(function(e) {
        if(e.which == 13) {
        var testervar = jQuery('[id^=\"titlediv\"]').find('#title');
        if (testervar.val().length < 1) {
            setTimeout("jQuery('#ajax-loading').css('visibility', 'hidden');", 100);
            if (!jQuery(".require-post").length) {
                jQuery('#post').before('<div class="error require-post"><p>' + eventmanager.require_title + '</p></div>');
            }
                setTimeout("jQuery('#publish').removeClass('button-primary-disabled');", 100);
                return false;
            } else {
                jQuery('.require-post').remove();
            }
        }
    });

});

/**
 * Format date object to yy-mm-dd
 */
function formattedDate (date){
    var curr_date = ("0" + date.getDate()).slice(-2);
    var curr_month = ("0" + (date.getMonth() + 1)).slice(-2)
    var curr_year = date.getFullYear();
    var fulldate = curr_year + "-" + curr_month + "-" + curr_date;
    return fulldate;
}

/**
 * Convert week days to digits
 */
function convertDays(dayOfTheWeek) {
    var weekday = new Array(7);
    weekday["Monday"] = 1;
    weekday["Tuesday"] = 2;
    weekday["Wednesday"] = 3;
    weekday["Thursday"] = 4;
    weekday["Friday"] = 5;
    weekday["Saturday"] = 6;
    weekday["Sunday"] = 7;
    var n = weekday[dayOfTheWeek];
    return n;
}

/**
 * Get week day that are closest to a choosen date.
 */
function getClosestDay(date, dayOfWeek) {
    var resultDate = new Date(date.getTime());
    resultDate.setDate(date.getDate() + (7 + dayOfWeek - date.getDay()) % 7);
    return resultDate;
}

/**
 * Creates data with values for ajax, and also runs the ajax
 * @param  int newValue either -1,0,1
 * @param  int postId   wordpress post id
 * @return void
 */
function changeAccepted(newValue, postId) {
    var data = {
        'action'    : 'my_action',
        'value'     : newValue,
        'postId'    : postId
    };

    var postElement = jQuery('#post-' + postId);
    toggleClasses(postElement, newValue);
    jQuery.post(ajaxurl, data, function(response) {
        console.log(response);
    });
}

/**
 * Changing the background of a event post
 * @param  jQuery object, base event element
 * @param  int responseValue
 * @return void
 */
function toggleClasses(element, responseValue) {
    if(responseValue == 1) {
        element.removeClass('red');
        element.addClass('green');
        element.find('.accept').addClass('hiddenElement');
        element.find('.deny').removeClass('hiddenElement');
    } else if(responseValue == -1) {
        element.removeClass('green');
        element.addClass('red');
        element.find('.accept').removeClass('hiddenElement');
        element.find('.deny').addClass('hiddenElement');
    }
}

/**
 * Hides event instructions if clicked.
 * @return void
 */
function dismissInstructions() {
    var data = {
        'action'    : 'dismiss'
    };

    jQuery.post(ajaxurl, data);
}

var ImportEvents = ImportEvents || {};

ImportEvents = ImportEvents || {};
ImportEvents.Parser = ImportEvents.Parser || {};

ImportEvents.Parser.Eventhandling = (function ($) {

    var newPosts            = {events:0,locations:0,contacts:0};
    var data                = {action:'import_events', value:'', api_keys:'', cron:false};
    var short               = 200;
    var long                = 400;
    var timerId             = null;
    var loadingOccasions    = false;
    var i                   = 0;
    var j                   = 0;

    function Eventhandling() {
        $(function() {

            $(document).on('click', '#xcap', function (e) {
                e.preventDefault();
                data.value = 'xcap';
                console.log('Parse XCAP');
                if (! loadingOccasions) {
                    loadingOccasions = true;
                    var button = $(this);
                    var storedCss = Eventhandling.prototype.collectCssFromButton(button);
                    Eventhandling.prototype.redLoadingButton(button, function() {
                        Eventhandling.prototype.parseEvents(data, button, storedCss);
                        return;
                    });
                }
            });

            $(document).on('click', '#cbis', function (e) {
                e.preventDefault();
                data.value = 'cbis';

                if (! loadingOccasions) {
                    loadingOccasions = true;
                    var button = $(this);
                    var storedCss = Eventhandling.prototype.collectCssFromButton(button);
                    Eventhandling.prototype.redLoadingButton(button, function() {
                        Eventhandling.prototype.parseEvents(data, button, storedCss);
                        return;
                    });
                }
            });

            $(document).on('click', '#cbislocation', function (e) {
                e.preventDefault();

                if (! loadingOccasions) {
                    loadingOccasions = true;
                    var button = $(this);
                    var storedCss = Eventhandling.prototype.collectCssFromButton(button);
                    Eventhandling.prototype.redLoadingButton(button, function() {
                        Eventhandling.prototype.parseCbislocation(data, button, storedCss);
                        return;
                    });
                }
            });

            $(document).on('click', '#occasions', function (e) {
                e.preventDefault();
                if (! loadingOccasions) {
                    loadingOccasions = true;
                    var button = $(this);
                    var storedCss = Eventhandling.prototype.collectCssFromButton(button);
                    Eventhandling.prototype.redLoadingButton(button, function() {
                        var data = {
                            'action'    : 'collect_occasions'
                        };

                        jQuery.post(ajaxurl, data, function(response) {
                            console.log(response);
                            loadingOccasions = false;
                            Eventhandling.prototype.restoreButton(button, storedCss);
                        });
                    });
                }
            });

        }.bind(this));
    }

    // Parse CBIS & XCAP events, loop through each API key
    Eventhandling.prototype.parseEvents = function(data, button, storedCss) {
        if (data.value === 'cbis') {
            data.api_keys = cbis_ajax_vars.cbis_keys[i];
        } else if (data.value === 'xcap') {
            data.api_keys = xcap_ajax_vars.xcap_keys[i];
        }

        // Show result if there's no API keys left to parse
        if( (typeof data.api_keys == 'undefined') ) {
            loadingOccasions = false;
            // Show data pop up if function is not called with cron
            if (! data.cron) {
                Eventhandling.prototype.dataPopUp(newPosts);
                Eventhandling.prototype.restoreButton(button, storedCss);
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
                Eventhandling.prototype.parseEvents(data, button, storedCss);
            }
        })
    };

    // Parse CBIS locations, loop through each API key and its categories
    Eventhandling.prototype.parseCbislocation = function(data, button, storedCss) {
        j = 0;

        // Show import result when done
        if( (typeof cbis_ajax_vars.cbis_keys[i] == 'undefined') ) {
            loadingOccasions = false;
            // Show data pop up if function is not called with cron
            if (! data.cron) {
                Eventhandling.prototype.dataPopUp(newPosts);
                Eventhandling.prototype.restoreButton(button, storedCss);
            }
            return;
        }

        data.api_keys = cbis_ajax_vars.cbis_keys[i];

        // Wait for callback and run this function again until there's no API keys left to parse
        $.when(Eventhandling.prototype.parseLocations(data)).then(function() {
            i++;
            Eventhandling.prototype.parseCbislocation(data, button, storedCss) ;
        });

    };

    // Parse each location category ID
    Eventhandling.prototype.parseLocations = function(data){
        var deferredObject = $.Deferred();

        Eventhandling.prototype.parse = function() {
            // Return when done
            if( (typeof data.api_keys.cbis_locations[j] == 'undefined') ) {
                deferredObject.resolve();
                return;
            }

            data.cbis_location = data.api_keys.cbis_locations[j];
            // Wait for Ajax callback and run this function again until there's no categories left
            $.when(Eventhandling.prototype.parseLocationCategory(data)).then(function() {
                j++;
                Eventhandling.prototype.parse(data);
            });
        };

        Eventhandling.prototype.parse();

        return deferredObject.promise();
    }

    // Call ajax with category ID
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
        })
    };

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
            timerId = setInterval(function()
            {
                if(counter > 3)
                    counter = 0;
                button.html(texts[counter]);
                ++counter;
            }, 500);
            if(callback != undefined)
                callback();
        });
    };

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

var ImportEvents = ImportEvents || {};

jQuery(document).ready(function ($) {
    if($('.acf-field-57ebb807988f8').length)
    {
        $('.acf-field-57ebb807988f8').append('<a class="createContact button" href="http://' + window.location.host + '/wp/wp-admin/post-new.php?post_type=contact&lightbox=true">' + eventmanager.new_contact + '</a>');
    }

    if($('.acf-field-57a9d5f3804e1').length)
    {
        $('.acf-field-57a9d5f3804e1').append('<a class="createContact button" href="http://' + window.location.host + '/wp/wp-admin/post-new.php?post_type=sponsor&lightbox=true">' + eventmanager.new_sponsor + '</a>');
    }

    if($('.acf-field-576117c423a52').length)
    {
        $('.acf-field-576117c423a52').append('<a class="createContact button" href="http://' + window.location.host + '/wp/wp-admin/post-new.php?post_type=location&lightbox=true">' + eventmanager.new_location + '</a>');
    }

    if($('.acf-field-57c7ed92054e6').length)
    {
        $('.acf-field-57c7ed92054e6').append('<a class="createContact button" href="http://' + window.location.host + '/wp/wp-admin/post-new.php?post_type=membership-card&lightbox=true">' + eventmanager.new_card + '</a>');
    }

    if($('.acf-field-581847f9642dc').length)
    {
        $('.acf-field-581847f9642dc').append('<a class="createContact button" href="http://' + window.location.host + '/wp/wp-admin/post-new.php?post_type=membership-card&lightbox=true">' + eventmanager.new_card + '</a>');
    }

    $('.openContact').click(function(event) {
        event.preventDefault();
        ImportEvents.Prompt.Modal.open($(this).attr('href'));
    });

    $('.createContact').click(function(event) {
        var parentId = $('#post_ID').val();
        event.preventDefault();
        ImportEvents.Prompt.Modal.open($(this).attr('href'), parentId);
    });
});

ImportEvents = ImportEvents || {};
ImportEvents.Prompt = ImportEvents.Prompt || {};

ImportEvents.Prompt.Modal = (function ($) {

    var isOpen = false;

    function Modal() {
        $(function() {
            this.handleEvents();
        }.bind(this));
    }

    Modal.prototype.open = function (url, parentId) {
        $('body').addClass('lightbox-open').append('\
            <div id="lightbox">\
                <div class="lightbox-wrapper">\
                    <button class="lightbox-close" data-lightbox-action="close">&times; ' + eventmanager.close + '</button>\
                    <iframe class="lightbox-iframe" src="' + url + '" frameborder="0" allowtransparency></iframe>\
                </div>\
            </div>\
        ');

        if(typeof(parentId) != 'undefined')
        {
            $(".lightbox-iframe").bind("load",function() {
                var newContactForm = $(this).contents().find('#post');
                newContactForm.append('<input type="hidden" id="parentId" name="parentId" value="' + parentId + '" />');
            });
        }

        isOpen = true;
    };

    Modal.prototype.close = function () {
        var modalElement = $('.lightbox-iframe');
        $('body').removeClass('lightbox-open');
        $('#lightbox').remove();
        isOpen = false;
    };

    Modal.prototype.handleEvents = function () {
        $(document).on('click', '[data-lightbox-action="close"]', function (e) {
            e.preventDefault();
            this.close();
        }.bind(this));
    };

    return new Modal();

})(jQuery);
