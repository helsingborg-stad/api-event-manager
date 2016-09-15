var ImportEvents = ImportEvents || {};
ImportEvents.loading = false;
ImportEvents.data = {'action' : 'import_events', 'value': ''};
ImportEvents.short = 200;
ImportEvents.long = 400;
ImportEvents.timerId = null;

jQuery(document).ready(function ($) {
    $('#cbis, #xcap').click(function() {
        if(!ImportEvents.loadingOccasions)
        {
            ImportEvents.loadingOccasions = true;
            var button = $(this);
            var storedCss = collectCssFromButton(button);
            redLoadingButton(button, function() {
                ImportEvents.data.value = button.attr('id');
                jQuery.post(ajaxurl, ImportEvents.data, function(response) {
                    var newPosts = response;
                    console.log(newPosts);
                    ImportEvents.loadingOccasions = false;
                    $('#blackOverlay').show();
                    var responsePopup = $('#importResponse');
                    responsePopup.show(500, function() {
                        var eventNumber = responsePopup.find('#event');
                        var locationNumber = responsePopup.find('#location');
                        var contactNumber = responsePopup.find('#contact');
                        var normalTextSize = eventNumber.css('fontSize');
                        var bigTextSize = '26px'
                        eventNumber.text(newPosts.events);
                        locationNumber.text(newPosts.locations);
                        contactNumber.text(newPosts.contacts);
                        eventNumber.animate({opacity: 1}, ImportEvents.long).animate({fontSize: bigTextSize}, ImportEvents.short).animate({fontSize: normalTextSize}, ImportEvents.short, function() {
                            locationNumber.animate({opacity: 1}, ImportEvents.long).animate({fontSize: bigTextSize}, ImportEvents.short).animate({fontSize: normalTextSize}, ImportEvents.short, function() {
                                contactNumber.animate({opacity: 1}, ImportEvents.long).animate({fontSize: bigTextSize}, ImportEvents.short).animate({fontSize: normalTextSize}, ImportEvents.short, function() {
                                    var loadingBar = responsePopup.find('#untilReload #meter');
                                    loadingBar.animate({width: '100%'}, 10000, function() {
                                        location.reload();
                                    });
                                });
                            });
                        });
                    });
                    restoreButton(button, storedCss);
                });
            });
        }
    });

    $('#occasions').click(function() {
        if(!ImportEvents.loadingOccasions)
        {
            ImportEvents.loadingOccasions = true;
            var button = $(this);
            var storedCss = collectCssFromButton(button);
            redLoadingButton(button, function() {
                var data = {
                    'action'    : 'collect_occasions'
                };

                jQuery.post(ajaxurl, data, function(response) {
                    console.log(response);
                    ImportEvents.loadingOccasions = false;
                    restoreButton(button, storedCss);
                });
            });
        }
    });

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
                    //console.log('Id: ' + id + ', Title: ' + title);
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
    if(pagenow == 'edit-event')
    {
        var eventUrl = admin_url + 'edit.php?post_type=event'
        var locationUrl = admin_url + 'edit.php?post_type=location'
        var contactUrl = admin_url + 'edit.php?post_type=contact'
        $('#wpwrap').append('<div id="blackOverlay"></div>');
        $('.wrap').append('\
            <div id="importResponse">\
                <div><p>New data created</p></div>\
                <div class="inline"><p>Events</p></div><div class="inline"><p>Locations</p></div><div class="inline"><p>Contacts</p></div>\
                <div class="inline"><p id="event">0</p></div><div class="inline"><p id="location">0</p></div><div class="inline"><p id="contact">0</p></div>\
                <div class="inline"><a class="button button-primary" href="' + eventUrl + '">Go to events</a></div><div class="inline"><a class="button button-primary" href="' + locationUrl + '">Go to locations</a></div><div class="inline"><a class="button button-primary" href="' + contactUrl + '">Go to contacts</a></div>\
                <div id="untilReload"><div id="meter"></div><p>Time until reload</p></div>\
            </div>\
        ');
    }
});

function collectCssFromButton(button)
{
    return {
        bgColor: button.css('background-color'),
        textColor: button.css('color'),
        textShadow: button.css('text-shadow'),
        boxShadow: button.css('box-shadow'),
        width: button.css('width'),
        text: button.text()
    };
}

function redLoadingButton(button, callback)
{
    button.fadeOut(500, function() {
        var texts = ['Loading&nbsp;&nbsp;&nbsp;', 'Loading.&nbsp;&nbsp;', 'Loading..&nbsp;', 'Loading...'];
        button.css('background-color', 'rgb(251,113,113)');
        button.css('color', 'black');
        button.css('text-shadow', 'none');
        button.css('box-shadow', 'none');
        button.css('width', '85px');
        button.html(texts[0]);
        button.fadeIn(500);

        var counter = 1;
        ImportEvents.timerId = setInterval(function()
        {
            if(counter > 3)
                counter = 0;
            button.html(texts[counter]);
            ++counter;
        }, 500);
        if(callback != undefined)
            callback();
    });
}

function restoreButton(button, storedCss)
{
    button.fadeOut(500, function() {
        button.css('background-color', storedCss.bgColor);
        button.css('color', storedCss.textColor);
        button.css('text-shadow', storedCss.textShadow);
        button.css('box-shadow', storedCss.boxShadow);
        button.css('width', storedCss.width);
        button.text(storedCss.text);
        button.fadeIn(500);
        clearTimeout(ImportEvents.timerId);
    });
}

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
        console.log(response);
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

ImportEvents = ImportEvents || {};
ImportEvents.Prompt = ImportEvents.Prompt || {};

ImportEvents.Prompt.Modal = (function ($) {

    console.log('First');
    var isOpen = false;

    function Modal() {
        console.log('new Modal!');
        $(function() {
            this.handleEvents();
        }.bind(this));
    }

    Modal.prototype.open = function (url, parentId) {

        console.log('Open');
        $('body').addClass('lightbox-open').append('\
            <div id="lightbox">\
                <div class="lightbox-wrapper">\
                    <button class="lightbox-close" data-lightbox-action="close">&times; ' + modularityAdminLanguage.close + '</button>\
                    <iframe class="lightbox-iframe" src="' + url + '" frameborder="0" allowtransparency></iframe>\
                </div>\
            </div>\
        ');

        if(typeof(parentId) != 'undefined')
        {
            console.log('Parent id set');
            $(".lightbox-iframe").bind("load",function() {
                var newContactForm = $(this).contents().find('#post');
                newContactForm.append('<input type="hidden" id="parentId" name="parentId" value="' + parentId + '" />')
            });
        }

        isOpen = true;
    };

    Modal.prototype.close = function () {
        console.log('Close');
        var modalElement = $('.lightbox-iframe');
        console.log(modalElement.find('#post_ID').val());
        console.log(modalElement.contents().find('#post').find('#post_ID').val());
        return;
    };

    Modal.prototype.handleEvents = function () {
        console.log('Handle events');
        $(document).on('click', '[data-lightbox-action="close"]', function (e) {
            console.log('Should not happen');
            e.preventDefault();
            this.close();
        }.bind(this));
    };

    return new Modal();

})(jQuery);
