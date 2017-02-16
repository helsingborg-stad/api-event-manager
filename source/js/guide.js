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
                        console.log(jsonResult);
                            jsonResult = this.objectToArray(jsonResult);

                        $("[data-name='guide_object_location'] select").each(function(index, object){
                            var select = $(this);

                            if(select.length) {
                                var prePopulateSelected = select.val();

                                //Empty select
                                select.empty();

                                //Populate from json
                                $.each(jsonResult, function(i, item) {
                                    select.append($('<option/>', {
                                        value: i,
                                        text: item
                                    }).bind(item));
                                }.bind(select));

                                //Select previusly selected option
                                if(prePopulateSelected != '') {
                                    select.val(prePopulateSelected);
                                }

                            }
                        });

                    }.bind(this));
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
