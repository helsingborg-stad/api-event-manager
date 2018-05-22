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
