var ImportEvents = ImportEvents || {};

jQuery(document).ready(function ($) {
    $('.accept').click(function() {
        var postId = $(this).attr('postid');
        changeAccepted(1, postId);
    });

    $('.deny').click(function() {
        var postId = $(this).attr('postid');
        changeAccepted(-1, postId);
    });
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

            jQuery.get('/json/wp/v2/' + pagenow + '?search=' + input, function(response) {
                $('#suggestionList').empty();
                for(var i in response) {
                    var id = response[i].id;
                    var title = response[i].title.rendered;
                    console.log('Id: ' + id + ', Title: ' + title);
                    $('#suggestionList').append('<li><a href="/wp/wp-admin/post.php?post=' + id + '&action=edit&lightbox=true" class="suggestion">' + title + '</a></li>');
                }
                if($('.suggestion').length == 0)
                    $('#suggestionContainer').hide();
                else
                {
                    $('#suggestionContainer').show();
                    $('.suggestion').click(function(event) {
                        event.preventDefault();
                        ImportEvents.Prompt.Modal.open($(this).attr('href'));
                    });
                }
            });
        }
        else
            $('#suggestionContainer').hide();

    });
    if(pagenow == 'contact' || pagenow == 'location' || pagenow == 'event' || pagenow == 'sponsor')
    {
        $('#titlewrap').after('<div id="suggestionContainer"><ul id="suggestionList"></ul></div>');
    }
});

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
