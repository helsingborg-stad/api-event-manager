ImportEvents = ImportEvents || {};
ImportEvents.Admin = ImportEvents.Admin || {};

ImportEvents.Admin.Fields = (function ($) {

    function Fields() {
        $(document).ready(function () {
            this.syncCheckBox();
        }.bind(this));
    }

    Fields.prototype.syncCheckBox = function() {
        $('#sync-meta-box input[type="checkbox"]').on('change', function () {
            if ($('#sync-meta-box input[type="checkbox"]').is(':checked')) {
                $('body').addClass('no-sync');
                $(this).parent().addClass('check_active');
                return;
            }

            $('body').removeClass('no-sync');
            $(this).parent().removeClass('check_active');
        }).trigger('change');
    };

    return new Fields();

})(jQuery);
