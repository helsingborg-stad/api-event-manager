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
        if(response == 1)
        {
            postElement.removeClass('red');
            postElement.addClass('green');
            postElement.find('.accept').addClass('hiddenElement');
            postElement.find('.deny').removeClass('hiddenElement');
        }
        else if(response == -1)
        {
            postElement.removeClass('green');
            postElement.addClass('red');
            postElement.find('.accept').removeClass('hiddenElement');
            postElement.find('.deny').addClass('hiddenElement');
        }
    });
}
