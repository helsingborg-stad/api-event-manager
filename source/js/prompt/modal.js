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
        $('body').addClass('lightbox-open').append('\
            <div id="lightbox">\
                <div class="lightbox-wrapper">\
                    <button class="lightbox-close" data-lightbox-action="close">&times; ' + eventmanager.close + '</button>\
                    <iframe class="lightbox-iframe" src="' + url + '" frameborder="0" allowtransparency></iframe>\
                </div>\
            </div>\
        ');

        if(typeof(parentId) != 'undefined')
        {
            $(".lightbox-iframe").bind("load",function() {
                var newContactForm = $(this).contents().find('#post');
                newContactForm.append('<input type="hidden" id="parentId" name="parentId" value="' + parentId + '" />');
            });
        }

        isOpen = true;
    };

    Modal.prototype.close = function () {
        var modalElement = $('.lightbox-iframe');
        $('body').removeClass('lightbox-open');
        $('#lightbox').remove();
        isOpen = false;
    };

    Modal.prototype.handleEvents = function () {
        $(document).on('click', '[data-lightbox-action="close"]', function (e) {
            e.preventDefault();
            this.close();
        }.bind(this));
    };

    return new Modal();

})(jQuery);
