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

function changeAccepted(newValue, postId) {
    var data = {
        'action' : 'my_action',
        'value' : newValue,
        'postId': postId
    };

    var postElement = jQuery('#post-' + postId);

    jQuery.post(ajaxurl, data, function(response) {
        toggleClasses(postElement, response);
    });
}

function toggleClasses(element, responseValue) {
    if(responseValue == 1)
    {
        element.removeClass('red');
        element.addClass('green');
        element.find('.accept').addClass('hiddenElement');
        element.find('.deny').removeClass('hiddenElement');
    }
    else if(responseValue == -1)
    {
        element.removeClass('green');
        element.addClass('red');
        element.find('.accept').removeClass('hiddenElement');
        element.find('.deny').addClass('hiddenElement');
    }
}
