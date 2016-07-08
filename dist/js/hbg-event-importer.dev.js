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

    toggleClasses(postElement, newValue);

    jQuery.post(ajaxurl, data, function(response) {
        console.log(response);
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

jQuery(document).ready(function ($) {

    $('.acf-field[data-name="sync"] input[type="checkbox"]').on('change', function () {
        if ($('.acf-field[data-name="sync"] input[type="checkbox"]').is(':checked')) {
            $('body').addClass('no-sync');
        } else {
            $('body').removeClass('no-sync');
        }

    }).trigger('change');

});

var ImportEvents = ImportEvents || {};

jQuery(document).ready(function ($) {
    if($('#acf-field_574d6f51c5204').length)
    {
        //add this class for a button instead of link 'page-title-action'
        $('#acf-field_574d6f51c5204').append('<a class="createContact" href="http://' + window.location.host + '/wp/wp-admin/post-new.php?post_type=contact&lightbox=true">Create new contact</a>');
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

        $('body').addClass('modularity-modal-open').append('\
            <div id="modularity-modal">\
                <div class="modularity-modal-wrapper">\
                    <button class="modularity-modal-close" data-modularity-modal-action="close">&times; ' + modularityAdminLanguage.close + '</button>\
                    <iframe class="modularity-modal-iframe" src="' + url + '" frameborder="0" allowtransparency></iframe>\
                </div>\
            </div>\
        ');

        if(typeof(parentId) != 'undefined')
        {
            $(".modularity-modal-iframe").bind("load",function() {
                var newContactForm = $(this).contents().find('#post');
                newContactForm.append('<input type="hidden" id="parentId" name="parentId" value="' + parentId + '" />')
            });
        }

        isOpen = true;
    };

    Modal.prototype.close = function () {
        console.log("closing");
        $('body').removeClass('modularity-modal-open');
        $('#modularity-modal').remove();
        isOpen = false;
        location.reload();
    };

    Modal.prototype.handleEvents = function () {
        $(document).on('click', '[data-modularity-modal-action="close"]', function (e) {
            console.log(e);
            e.preventDefault();
            this.close();
        }.bind(this));
    };

    return new Modal();

})(jQuery);
