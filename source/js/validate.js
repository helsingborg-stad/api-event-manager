ImportEvents = ImportEvents || {};
ImportEvents.Admin = ImportEvents.Admin || {};

ImportEvents.Admin.Validate = (function ($) {
    function Validate() {
        $(document).on('click', '#publish', function (e) {
            var image = this.image();
            var title = this.post_title();
            return (image && title) ? true : false;
        }.bind(this));

        $(document).on('keypress', 'input[name="post_title"]', function (e) {
            if (e.which != 13) {
                return true;
            }

            var image = this.image();
            var title = this.post_title();
            return (image && title) ? true : false;
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
