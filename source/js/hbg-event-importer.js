jQuery(document).ready(function ($) {

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
        if (e.which == 13) {
            var testervar = $('[id^=\"titlediv\"]').find('#title');

            if (testervar.val().length < 1) {
                setTimeout(function () {
                    jQuery('#ajax-loading').css('visibility', 'hidden');
                }, 100);

                if (!jQuery(".require-post").length) {
                    $('#post').before('<div class="error require-post"><p>' + eventmanager.require_title + '</p></div>');
                }

                setTimeout(function () {
                    $('#publish').removeClass('button-primary-disabled');
                }, 100);

                return false;
            }

            jQuery('.require-post').remove();
        }
    });
});
