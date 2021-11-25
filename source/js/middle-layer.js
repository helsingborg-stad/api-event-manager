ImportEvents = ImportEvents || {};
ImportEvents.Admin = ImportEvents.Admin || {};

ImportEvents.Admin.MiddleLayerSync = (function ($) {
    function MiddleLayerSync() {
        $(function(){
            this.handleEvents();
        }.bind(this));
    }

    /**
     * Handle events
     * @return void
     */
  MiddleLayerSync.prototype.handleEvents = function () {
        $(document).on('click', '#populate-middle-layer', function (e) {
            e.preventDefault();
          $(this).prop('disabled', true).text(eventmanager.middle_layer_synchronization_scheduled);
            $.ajax({
                url: eventmanager.ajaxurl,
                type: 'post',
                data: {
                  action: 'schedule_populate_middle_layer'
                }
            });
        });
    };

  return new MiddleLayerSync();

})(jQuery);
