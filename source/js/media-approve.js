ImportEvents = ImportEvents || {};
ImportEvents.Admin = ImportEvents.Admin || {};

ImportEvents.Admin.MediaApprove = (function ($) {

    function MediaApprove() {
        $(function() {
            var $imageBox = $('div#postimagediv > .inside');

            if (pagenow !== 'event' || $imageBox.find('img').length > 0) {
                return;
            }

            this.addCheckBoxes($imageBox);
            this.handleEvents($imageBox);
        }.bind(this));
    }

    MediaApprove.prototype.addCheckBoxes = function(imageBox) {
        imageBox.children().hide();
        $('.acf-gallery-add').attr('disabled', true).prop('disabled', true);
        var checkBoxes =  '<div id="image-approve"><p><strong>' + eventmanager.confirm_statements + '</strong></p>';
            checkBoxes += '<input type="checkbox" name="approve" id="first-approve" value="1">';
            checkBoxes += '<span> ' + eventmanager.promote_event + '</span>';
            checkBoxes += '<p>' + eventmanager.identifiable_persons + '</p>';
            checkBoxes += '<p><input type="radio" name="approve" value="1">' + eventmanager.yes + ' <input type="radio" name="approve" value="0">' + eventmanager.no + '</p>';
            checkBoxes += '<div id="persons-approve" class="hidden"><input type="checkbox" name="approve" id="second-approve" value="1">';
            checkBoxes += '<span> ' + eventmanager.persons_approve + '</span></div></div>';
        imageBox.append(checkBoxes);
    };

    /**
     * Handle events
     * @return void
     */
    MediaApprove.prototype.handleEvents = function(imageBox) {
        $('input:radio[name=approve]').change(function() {
            if (this.value == 1) {
                $('#persons-approve').removeClass('hidden');
            } else {
                $('#persons-approve').addClass('hidden');
            }
        });

        $('input[name=approve]').change(function() {
            var firstCheck  = $('input:checkbox[id=first-approve]:checked').length > 0,
                radioCheck  = $('input:radio[name=approve]:checked').val(),
                secondCheck = $('input:checkbox[id=second-approve]:checked').length > 0;
            if (firstCheck && radioCheck == 0 || firstCheck && secondCheck) {
                $('#image-approve').remove();
                imageBox.find(':hidden').fadeIn();
                $('.acf-gallery-add').attr('disabled', false).prop('disabled', false);
            }
        });
    };

    return new MediaApprove();

})(jQuery);
