jQuery(document).ready(function ($) {
    $('.accept').click(function() {
        var postId = $(this).attr('postid');

        changeAccepted(1, postId);
        /*$.post(ajaxurl, data, function(response) {
            postElement.css('background-color', 'rgb(118,245,120)');
        });*/
    });

    $('.deny').click(function() {
        var postId = $(this).attr('postid');
        changeAccepted(-1, postId);
        /*$.post(ajaxurl, data, function(response) {
            postElement.css('background-color', 'rgb(251,113,113)');
        });*/
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
        console.log(response);
        if(response == 1)
            postElement.css('background-color', 'rgb(118,245,120)');
        else if(response == -1)
            postElement.css('background-color', 'rgb(251,113,113)');
    });
}
