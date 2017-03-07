ImportEvents = ImportEvents || {};
ImportEvents.Admin = ImportEvents.Admin || {};

ImportEvents.Admin.Guide = (function ($) {

    var select2_args_internal = {};

    function Guide() {
        $(document).ready(function () {

            if (pagenow === 'guide') {
                this.onlySublocations();

                // Populate objects when clicking the beacon tab
                $(document).on('click', '.acf-tab-button[data-key="field_58ab0c6354b09"]', function () {
                    this.populateBeaconObjects();
                }.bind(this));

                // Populate objects when clicking the "add new row" button in beacon tab
                $(document).on('click', '[data-name="guide_beacon"] [data-event="add-row"]', function () {
                    setTimeout(function () {
                        var $row = $('[data-name="guide_beacon"] .acf-row:not(.acf-clone)').last();
                        var $input = $row.find('[data-name="objects"] .acf-input input');

                        $input.select2('destroy');
                        $input.select2({
                            data: this.getObjects(),
                            multiple: true
                        });
                    }.bind(this), 1);
                }.bind(this));
            }

        }.bind(this));
    }

    /**
     * Only use sublocations in locations selector
     * @return {void}
     */
    Guide.prototype.onlySublocations = function() {
        acf.add_filter('select2_ajax_data', function( data, args, $input, $field ){
            if (data.field_key !== 'field_58ab0c9554b0a') {
                return data;
            }

            var groupInputId = 'acf-field_589dd138aca7e-input';
            var selectedGroup = $('#' + groupInputId).val();

            if (selectedGroup) {
                data.selectedGroup = selectedGroup;
            }

            // return
            return data;
        });
    };

    /**
     * Populate objects selectors
     * @return {void}
     */
    Guide.prototype.populateBeaconObjects = function() {
        $('[data-name="objects"] select ~ input[type="hidden"]').each(function (index, element) {
            $(element).select2('destroy');
            $(element).select2({
                data: this.getObjects(),
                multiple: true
            });
        }.bind(this));
    };

    /**
     * Get objects from the Content Objects tab to fill the objects selector with
     * @return {array}
     */
    Guide.prototype.getObjects = function() {
        var objects = [];

        $('[data-name="guide_content_objects"] table tbody').first().children('tr.acf-row:not(.acf-clone)').each(function (index, item) {
            var $item = $(item);

            var uid = $item.find('[data-name="guide_object_uid"] input').val();
            var title = $item.find('[data-name="guide_object_title"] input').val();

            if (!uid) {
                uid = new Date().valueOf();
                $item.find('[data-name="guide_object_uid"] input').val(uid);
            }

            objects.push({
                id: uid,
                text: title
            });
        });

        return objects;
    };

    return new Guide();

})(jQuery);
