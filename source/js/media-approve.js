ImportEvents = ImportEvents || {};
ImportEvents.Admin = ImportEvents.Admin || {};

ImportEvents.Admin.MediaApprove = (function ($) {

    function MediaApprove() {
        $(function() {
            if (pagenow !== 'event') {
                return;
            }

            this.addCheckBoxes();
            this.handleEvents();
        }.bind(this));
    }

    MediaApprove.prototype.addCheckBoxes = function() {
        var $imageBox = $('div#postimagediv > .inside');

        if ($imageBox.find('img').length === 0) {
            $imageBox.children().hide();
            var checkBoxes =  '<div class="image-approve"><p><strong>För att ladda upp en bild behöver du bekräfta nedanstående punkter.</strong></p>';
                checkBoxes += '<input type="checkbox" name="approve" value="1">';
                checkBoxes += '<span> Jag har rätt att använda denna bild för att marknadsföra detta evenemang.</span>';
                checkBoxes += '<p>Finns det identifierbara personer på bilden/bilderna?</p>';
                checkBoxes += '<p><input type="radio" name="radio-persons" value="yes">Yes <input type="radio" name="radio-persons" value="no">No</p>';
                checkBoxes += '<input type="checkbox" name="approve" value="1">';
                checkBoxes += '<span> De har godkänt att bilden används för att marknadsföra detta evenemang och har informerats om att  efter att bilden lagts in i plats- och eventdatabasen kan komma att synas i olika kanaler för att marknadsföra evenemanget.</span></div>';
            $imageBox.append(checkBoxes);
        }
    };

    /**
     * Handle events
     * @return void
     */
    MediaApprove.prototype.handleEvents = function() {
        $('input:radio[name=radio-persons]').change(function() {
            if (this.value == 'yes') {
                console.log("Yes");
            } else {
                console.log("No");
            }
        });

    };

    return new MediaApprove();

})(jQuery);
