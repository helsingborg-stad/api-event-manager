ImportEvents = ImportEvents || {};
ImportEvents.Admin = ImportEvents.Admin || {};

ImportEvents.Admin.ColorPicker = (function ($) {

    function ColorPicker() {
        this.initColorPicker();
    }

    ColorPicker.prototype.initColorPicker = function() {
        $(".colorpicker input").spectrum({
            flat: true,
            showInput: true
        });
    };

    return new ColorPicker();

})(jQuery);
object
