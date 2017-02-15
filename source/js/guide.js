ImportEvents = ImportEvents || {};
ImportEvents.Admin = ImportEvents.Admin || {};

ImportEvents.Admin.Guide = (function ($) {

    function Guide() {
        this.locationPicker();
    }

    Guide.prototype.locationPicker = function() {
        jQuery(function($){
            $(document).on('change','#acf-field_589498b7fc7b3-input', function() {

                var postId = this.getParameterByName('post');

                var data = {
                    'action': 'update_guide_sublocation_option',
                    'selected': $('#acf-field_589498b7fc7b3-input').val(),
                    'post_id': postId
                };

                if(postId) {
                    $.post(ajaxurl, data, function(response) {
                        var jsonResult = $.parseJSON(response);

                        jQuery("[data-name='guide_object_location']").each(function(){
                            var prePopulateSelected = $(this).val();


                        });
                    });
                } else {
                    alert("Please save the guide to allow sublocations to be selected.");
                }
            }.bind(this));
        }.bind(this));
    };

    Guide.prototype.getParameterByName = function(name) {
        var results = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(window.location.href);
        if (!results) {
            return undefined;
        }
        return results[1] || undefined;
    };

    return new Guide();

})(jQuery);
