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

jQuery(document).ready(function ($) {

    $('.acf-field[data-name="sync"] input[type="checkbox"]').on('change', function () {
        if ($('.acf-field[data-name="sync"] input[type="checkbox"]').is(':checked')) {
            $('body').addClass('no-sync');
        } else {
            $('body').removeClass('no-sync');
        }

    }).trigger('change');

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
        $('body').removeClass('modularity-modal-open');
        $('#modularity-modal').remove();
        isOpen = false;
        location.reload();
    };

    Modal.prototype.handleEvents = function () {
        $(document).on('click', '[data-modularity-modal-action="close"]', function (e) {
            e.preventDefault();
            this.close();
        }.bind(this));
    };

    return new Modal();

})(jQuery);

var ImportEvents = ImportEvents || {};

jQuery(document).ready(function ($) {
    if($('#acf-field_576116fd23a4f').length)
    {
        //add this class for a button instead of link 'page-title-action'
        $('#acf-field_576116fd23a4f').append('<a class="createContact button button-primary" href="http://' + window.location.host + '/wp/wp-admin/post-new.php?post_type=contact&lightbox=true">Create new contact</a>');
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
