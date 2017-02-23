jQuery(document).ready(function ($) {


    $('.notice.is-dismissible').on('click', '.notice-dismiss', function(event){
        dismissInstructions();
    });

    var oldInput = '';
    $('input[name="post_title"]').on('change paste keyup', function() {
        var input = $(this).val();

        if (input == oldInput) {
            return;
        }

        oldInput = input;
        if(input.length > 3) {
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

                for (var i in response) {
                    var id = response[i].id;
                    var title = (isevent) ? response[i].title : response[i].title.rendered;
                    var pageText = title.replace("<span>","").replace("</span>"),
                    regex = new RegExp("(" + input + ")", "igm"),
                    highlighted = pageText.replace(regex ,"<span>$1</span>");
                    $('#suggestionList').append('<li><a href="/wp/wp-admin/post.php?post=' + id + '&action=edit" class="suggestion">' + highlighted + '</a></li>');
                }

                if ($('.suggestion').length == 0) {
                    $('#suggestionContainer').fadeOut(200);
                } else {
                    $('#suggestionList').prepend('<li><strong>' + eventmanager.similar_posts + ': <button class="notice-dismiss suggestion-hide" suggestion-hide-action="close"> </strong></li>');
                    $('#suggestionContainer').fadeIn(200);
                }
            });
        } else {
            $('#suggestionContainer').fadeOut(200);
        }
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
 * Hides event instructions if clicked.
 * @return void
 */
function dismissInstructions() {
    var data = {
        'action'    : 'dismiss'
    };

    jQuery.post(ajaxurl, data);
}
