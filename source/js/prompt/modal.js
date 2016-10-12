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
        console.log('Open iframe');
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
            console.log(parentId);
            $(".lightbox-iframe").bind("load",function() {
                var newContactForm = $(this).contents().find('#post');
                newContactForm.append('<input type="hidden" id="parentId" name="parentId" value="' + parentId + '" />');
            });
        }

        isOpen = true;
    };

    Modal.prototype.close = function () {
        console.log('Close');
        var modalElement = $('.lightbox-iframe');
        console.log(modalElement.find('#post_ID').val());
        console.log(modalElement.contents().find('#post').find('#post_ID').val());
        $('.lightbox-iframe').remove();
        $('#lightbox').remove();
        $('body').removeClass('lightbox-open');
        return;
    };

    Modal.prototype.handleEvents = function () {
        console.log('Handle events');
        $(document).on('click', '[data-lightbox-action="close"]', function (e) {
            console.log('Should not happen');
            e.preventDefault();
            this.close();
        }.bind(this));

        // $(document).keydown(function(e) {
        //     if (e.key == "Escape") {
        //         console.log('Esc pressed');
        //         $('[data-lightbox-action="close"]').click();
        //         return false;
        //     }
        // }.bind(this));

    };

    return new Modal();

})(jQuery);
