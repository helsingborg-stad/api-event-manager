jQuery(document).ready(function ($) {
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
