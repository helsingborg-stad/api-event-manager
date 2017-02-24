ImportEvents = ImportEvents || {};
ImportEvents.Admin = ImportEvents.Admin || {};

ImportEvents.Admin.Validate = (function ($) {
    function Validate() {
        $(document).on('click', '#publish', function (e) {
            return this.post_title(e);
        }.bind(this));

        $(document).on('keypress', 'input[name="post_title"]', function (e) {
            if (e.which != 13) {
                return true;
            }

            return this.post_title(e);
        }.bind(this));
    }

    Validate.prototype.post_title = function(e) {
        var $title = $('#title');

        if ($title.val().length > 0) {
            $('.require-post').remove();
            return true;
        }

        setTimeout(function () {
            $('#ajax-loading').css('visibility', 'hidden');
        }, 100);

        setTimeout(function () {
            $('#publish').removeClass('button-primary-disabled');
        }, 100);

        if (!$(".require-post").length) {
            $('#post').before('<div class="error require-post"><p>' + eventmanager.require_title + '</p></div>');
        }

        return false;
    };

    return new Validate();

})(jQuery);
