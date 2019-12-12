ImportEvents = ImportEvents || {};
ImportEvents.Admin = ImportEvents.Admin || {};

ImportEvents.Admin.AcceptDeny = (function ($) {

    function AcceptDeny() {
        $(function(){
            this.handleEvents();
        }.bind(this));
    }

    /**
     * Accept or deny events.
     * @param  int postStatus 1 = accept, 0 = deny
     * @param  int postId     event object id
     * @return void
     */
    AcceptDeny.prototype.changeAccepted = function(postStatus, postId) {
        $.ajax({
            url: eventmanager.ajaxurl,
            type: 'post',
            data: {
                action    : 'accept_or_deny',
                value     : postStatus,
                postId    : postId
            },
            beforeSend: function(response) {
                var postElement = $('#post-' + postId);

                if (postStatus === 1) {
                    postElement.find('.deny').removeClass('hidden');
                    postElement.find('.accept').addClass('hidden');
                } else if(postStatus === 0) {
                    postElement.find('.deny').addClass('hidden');
                    postElement.find('.accept').removeClass('hidden');
                }
            }
        });
    };

    /**
     * Handle events
     * @return void
     */
    AcceptDeny.prototype.handleEvents = function () {
        $(document).on('click', '.accept', function (e) {
            e.preventDefault();
            var postId = $(e.target).closest('.accept').attr('post-id');
            this.changeAccepted(1, postId);
        }.bind(this));

        $(document).on('click', '.deny', function (e) {
            e.preventDefault();
            var postId = $(e.target).closest('.deny').attr('post-id');
            this.changeAccepted(0, postId);
        }.bind(this));
    };

    return new AcceptDeny();

})(jQuery);

var ImportEvents = ImportEvents || {};
ImportEvents = ImportEvents || {};
ImportEvents.Admin = ImportEvents.Admin || {};

ImportEvents.Admin.Import = (function ($) {

    function Import() {
        $(function(){
            this.handleEvents();
        }.bind(this));
    }

    /**
     * Handle events
     * @return void
     */
    Import.prototype.handleEvents = function () {
        $(document).on('click', '.single-import', function (e) {
            e.preventDefault();
            $(this).prop('disabled', true).text(eventmanager.import_scheduled);
            $.ajax({
                url: eventmanager.ajaxurl,
                type: 'post',
                data: {
                    action: 'schedule_single_import',
                    client: this.dataset.client
                }
            });
        });
    };

    return new Import();

})(jQuery);

ImportEvents = ImportEvents || {};
ImportEvents.Admin = ImportEvents.Admin || {};

ImportEvents.Admin.MediaApprove = (function($) {
    function MediaApprove() {
        $(
            function() {
                var $imageBox = $('div#postimagediv > .inside');

                if (pagenow !== 'event' || $imageBox.find('img').length > 0) {
                    return;
                }

                this.addCheckBoxes($imageBox);
                this.handleEvents($imageBox);
            }.bind(this)
        );
    }

    MediaApprove.prototype.addCheckBoxes = function(imageBox) {
        imageBox.children().hide();
        $('.acf-gallery-add')
            .attr('disabled', true)
            .prop('disabled', true);
        var checkBoxes =
            '<div id="image-approve"><p><strong>' +
            eventmanager.confirm_statements +
            '</strong></p>';
        checkBoxes += '<input type="checkbox" name="approve" id="first-approve" value="1">';
        checkBoxes += '<span> ' + eventmanager.promote_event + '</span>';
        checkBoxes += '<p>' + eventmanager.identifiable_persons + '</p>';
        checkBoxes +=
            '<p><input type="radio" name="approve" value="1">' +
            eventmanager.yes +
            ' <input type="radio" name="approve" value="0">' +
            eventmanager.no +
            '</p>';
        checkBoxes +=
            '<div id="persons-approve" class="hidden"><input type="checkbox" name="approve" id="second-approve" value="1">';
        checkBoxes += '<span> ' + eventmanager.persons_approve + '</span></div></div>';
        imageBox.append(checkBoxes);
    };

    /**
     * Handle events
     * @return void
     */
    MediaApprove.prototype.handleEvents = function(imageBox) {
        $('input:radio[name=approve]').change(function() {
            if (this.value == 1) {
                $('#persons-approve').removeClass('hidden');
            } else {
                $('#persons-approve').addClass('hidden');
            }
        });

        $('input[name=approve]').change(function() {
            var firstCheck = $('input:checkbox[id=first-approve]:checked').length > 0,
                radioCheck = $('input:radio[name=approve]:checked').val(),
                secondCheck = $('input:checkbox[id=second-approve]:checked').length > 0;
            if ((firstCheck && radioCheck == 0) || (firstCheck && secondCheck)) {
                $('#image-approve').remove();
                imageBox.find(':hidden').fadeIn();
                $('.acf-gallery-add')
                    .attr('disabled', false)
                    .prop('disabled', false);
            }
        });
    };

    return new MediaApprove();
})(jQuery);

ImportEvents = ImportEvents || {};
ImportEvents.Admin = ImportEvents.Admin || {};

ImportEvents.Admin.RestrictDatePicker = (function($) {
    function RestrictDatePicker() {
        if (pagenow !== 'edit-event') {
            return;
        }

        this.init();
    }

    /**
     * Init datepicker
     * @return void
     */
    RestrictDatePicker.prototype.init = function() {
        $(document).ready(function() {
            var from = $('input[name="restrictDateFrom"]'),
                to = $('input[name="restrictDateTo"]');

            from.datepicker({ dateFormat: 'dd-mm-yy' });
            to.datepicker({ dateFormat: 'dd-mm-yy' });

            from.on('change', function() {
                to.datepicker('option', 'minDate', from.val());
            });

            to.on('change', function() {
                from.datepicker('option', 'maxDate', to.val());
            });
        });
    };

    return new RestrictDatePicker();
})(jQuery);

ImportEvents = ImportEvents || {};
ImportEvents.Admin = ImportEvents.Admin || {};

ImportEvents.Admin.Suggestions = (function ($) {

    var typingTimer;
    var lastTerm;
    var suggestionString;

    var acceptedPagenow = [
        'organizer',
        'location',
        'event',
        'sponsor',
        'package',
        'membership-card',
        'guide'
    ];

    function Suggestions() {
        if (acceptedPagenow.indexOf(pagenow) < 0) {
            return;
        }

        this.switchName();

        $(document).on('keyup', 'input[name="post_title"]', function (e) {
            var $this = $(e.target);

            clearTimeout(typingTimer);

            typingTimer = setTimeout(function() {
                this.search($this.val());
            }.bind(this), 300);
        }.bind(this));

        $(document).on('click', '[data-action="suggestions-close"]', function (e) {
            e.preventDefault();
            this.dismiss();
        }.bind(this));
    }

    /**
     * Performs the search for similar titles
     * @param  {string} term Search term
     * @return {void}
     */
    Suggestions.prototype.search = function(term) {
        if (term.length <= 3 || term === lastTerm) {
            return false;
        }

        // Set last term to the current term
        lastTerm = term;

        // Get API endpoint for performning the search
        var geturl = eventmanager.wpapiurl + '/wp/v2/' + pagenow + '?search=' + term;

        if (pagenow === 'event') {
            geturl = eventmanager.wpapiurl + '/wp/v2/' + pagenow + '/search?term=' + term;
        }

        // Do the search request
        $.get(geturl, function(response) {
            if (!response.length) {
                this.dismiss();
                return;
            }

            this.output(response, term);
        }.bind(this), 'JSON');
    };

    /**
     * Outputs the title suggestions
     * @param  {array} suggestions
     * @param  {string} term
     * @return {void}
     */
    Suggestions.prototype.output = function(suggestions, term) {
        var $suggestions = $('#title-suggestions');

        if (!$suggestions.length) {
            $suggestions = $('<div id="title-suggestions"></div>');
            $suggestions.append('<ul></ul>');
        }

        $suggestions.find('ul').empty();

        $suggestions.find('ul').append('<li><strong>' + suggestionString + ':</strong> <button type="button" class="notice-dismiss suggestion-hide" data-action="suggestions-close"></button></li>');

        $.each(suggestions, function (index, suggestion) {
            var title = pagenow === 'event' ? suggestion.title : suggestion.title.rendered;
            var pageText = title.replace("<span>","").replace("</span>"),
            regex = new RegExp("(" + term + ")", "igm"),
            highlighted = pageText.replace(regex ,"<span>$1</span>");

            $suggestions.find('ul').append('<li><a href="' + eventmanager.adminurl + 'post.php?post=' + suggestion.id + '&action=edit" class="suggestion">' + highlighted + '</a></li>');
        });

        $('#titlewrap').append($suggestions);
        $suggestions.slideDown(200);
    };

    /**
     * Dismisses the suggestions
     * @return {void}
     */
    Suggestions.prototype.dismiss = function() {
        $('#title-suggestions').slideUp(200, function () {
            $('#title-suggestions').remove();
        });
    };

    Suggestions.prototype.switchName = function() {
        switch(pagenow) {
            case 'organizer':
                suggestionString = eventmanager.organizers + ' ' + eventmanager.with_similar_name;
                break;
            case 'location':
                suggestionString = eventmanager.locations + ' ' + eventmanager.with_similar_name;
                break;
            case 'sponsor':
                suggestionString = eventmanager.sponsors + ' ' + eventmanager.with_similar_name;
                break;
            case 'package':
                suggestionString = eventmanager.packages + ' ' + eventmanager.with_similar_name;
                break;
            case 'membership-card':
                suggestionString = eventmanager.membership_cards + ' ' + eventmanager.with_similar_name;
                break;
            case 'guide':
                suggestionString = eventmanager.guides + ' ' + eventmanager.with_similar_name;
                break;
            default:
                suggestionString = eventmanager.events + ' ' + eventmanager.with_similar_name;
        }
    };

    return new Suggestions();

})(jQuery);

ImportEvents = ImportEvents || {};
ImportEvents.Admin = ImportEvents.Admin || {};

ImportEvents.Admin.Validate = (function ($) {
    function Validate() {
        $(document).on('click', '#publish', function (e) {
            return this.post_title();
        }.bind(this));

        $(document).on('keypress', 'input[name="post_title"]', function (e) {
            if (e.which != 13) {
                return true;
            }

            return this.post_title();
        }.bind(this));
    }

    Validate.prototype.image = function() {
        var $imported = $('body').hasClass('imported');
        if (! $imported) {
            var $img = $('#postimagediv').find('img');
            if ($img.length > 0) {
                $('.require-image').remove();
            } else {
                var message = eventmanager.require_image
                this.showError('image', message);
                return false;
            }
        }

        return true;
    };

    Validate.prototype.post_title = function() {
        var $title = $('#title');
        if ($title.val().length > 0) {
            $('.require-title').remove();
        } else {
            var message = eventmanager.require_title
            this.showError('title', message);
            return false;
        }

        return true;
    };

    Validate.prototype.showError = function(field, message){
        setTimeout(function () {
            $('#ajax-loading').css('visibility', 'hidden');
        }, 100);

        setTimeout(function () {
            $('#publish').removeClass('button-primary-disabled');
        }, 100);

        if (!$(".require-" + field).length) {
            $('#post').before('<div class="error require-' + field + '"><p>' + message + '</p></div>');
        }
    };

    return new Validate();

})(jQuery);

ImportEvents = ImportEvents || {};
ImportEvents.Prompt = ImportEvents.Prompt || {};

ImportEvents.Prompt.Modal = (function ($) {

    var isOpen = false;

    function Modal() {
        $(function() {
            this.handleEvents();
        }.bind(this));
    }

    /**
     * Open modal
     * @param  {string} url      Url to open
     * @param  {} parentId
     * @return {void}
     */
    Modal.prototype.open = function (url, parentId) {
        $('body').addClass('lightbox-open').append('\
            <div id="lightbox">\
                <div class="lightbox-wrapper">\
                    <button class="lightbox-close" data-lightbox-action="close">&times; ' + eventmanager.close + '</button>\
                    <iframe class="lightbox-iframe" src="' + url + '" frameborder="0" allowtransparency></iframe>\
                </div>\
            </div>\
        ');

        if (typeof(parentId) != 'undefined') {
            $(".lightbox-iframe").bind("load", function() {
                var newContactForm = $(this).contents().find('#post');
                newContactForm.append('<input type="hidden" id="parentId" name="parentId" value="' + parentId + '" />');
            });
        }

        isOpen = true;
    };

    /**
     * Close modal
     * @return {void}
     */
    Modal.prototype.close = function () {
        var modalElement = $('.lightbox-iframe');

        $('body').removeClass('lightbox-open');
        $('#lightbox').remove();

        isOpen = false;
    };

    /**
     * Handle events
     * @return {void}
     */
    Modal.prototype.handleEvents = function () {
        $(document).on('click', '[data-lightbox-action="close"]', function (e) {
            e.preventDefault();
            this.close();
        }.bind(this));
    };

    return new Modal();

})(jQuery);

ImportEvents = ImportEvents || {};
ImportEvents.Admin = ImportEvents.Admin || {};

ImportEvents.Admin.NewPostModal = (function ($) {

    function NewPostModal() {
        $(function(){

            this.createTrigger('location', '.acf-field-576117c423a52');
            this.createTrigger('organizer', '.acf-field-5922a161ab32f');
            this.createTrigger('sponsor', '.acf-field-57a9d5f3804e1');
            this.createTrigger('membership-card', '.acf-field-57c7ed92054e6');
            this.createTrigger('membership-card', '.acf-field-581847f9642dc');

            this.bindLaunchModal();

        }.bind(this));
    }

    /**
     * Create button to trigger new post modal
     * @param  string   posttype to create
     * @param  string   triggering class or id
     * @return void
     */
    NewPostModal.prototype.createTrigger = function(postType, triggerClass) {
        if ($(triggerClass).length) {
            if (typeof eventmanager['new_' + postType] !== 'undefined') {
               $(triggerClass).append('<a class="createNewPost button" href="' + eventmanager.adminurl + 'post-new.php?post_type=' + postType+ '&lightbox=true">' + eventmanager['new_' + postType] + '</a>');
            }
        }
    };

    /**
     * Hook on trigger button to launch modal
     * @return void
     */
    NewPostModal.prototype.bindLaunchModal = function() {
        $(document).on('click','.createNewPost', function(e) {
            e.preventDefault();
            ImportEvents.Prompt.Modal.open($(this).attr('href'), $('#post_ID').val());
        });
    };

    return new NewPostModal();

})(jQuery);

ImportEvents = ImportEvents || {};
ImportEvents.Prompt = ImportEvents.Prompt || {};

ImportEvents.Prompt.Notice = (function ($) {

    function Notice() {
        $(document).on('click', '.event-guidelines .notice-dismiss', function (e) {
            this.dismissInstructions();
        }.bind(this));
    }

    Notice.prototype.dismissInstructions = function() {
        var data = {
            action: 'dismiss'
        };

        $.post(ajaxurl, data);
    };

    return new Notice();

})(jQuery);
