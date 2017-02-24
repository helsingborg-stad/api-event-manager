ImportEvents = ImportEvents || {};
ImportEvents.Prompt = ImportEvents.Prompt || {};

ImportEvents.Prompt.Notice = (function ($) {

    function Notice() {
        $('.notice.is-dismissible').on('click', '.notice-dismiss', function(event){
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
