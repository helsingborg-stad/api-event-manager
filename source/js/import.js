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
