jQuery(document).ready(function ($) {
    $('.accept').click(function() {
        var postId = $(this).attr('postid');
        changeAccepted(1, postId);
    });

    $('.deny').click(function() {
        var postId = $(this).attr('postid');
        changeAccepted(-1, postId);
    });
});

/**
 * Creates data with values for ajax, and also runs the ajax
 * @param  int newValue either -1,0,1
 * @param  int postId   wordpress post id
 * @return void
 */
function changeAccepted(newValue, postId) {
    var data = {
        'action' : 'my_action',
        'value' : newValue,
        'postId': postId
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
