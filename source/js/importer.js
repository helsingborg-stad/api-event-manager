var ImportEvents = ImportEvents || {};

ImportEvents = ImportEvents || {};
ImportEvents.Parser = ImportEvents.Parser || {};

ImportEvents.Parser.Eventhandling = (function ($) {

    var i                   = 0;
    var newPosts            = {events:0,locations:0,contacts:0};
    var data                = {action : 'import_events',
                                value : '',
                                api_keys : cbis_ajax_vars.cbis_keys[i]
                              };
    var short               = 200;
    var long                = 400;
    var timerId             = null;
    var loadingOccasions    = false;

    function Eventhandling() {
        $(function() {

            $(document).on('click', '#cbis, #xcap, #cbislocation', function (e) {
                e.preventDefault();

                if (! loadingOccasions) {
                    loadingOccasions = true;
                    var button = $(this);
                    var storedCss = Eventhandling.prototype.collectCssFromButton(button);
                    Eventhandling.prototype.redLoadingButton(button, function() {
                        data.value = button.attr('id');

                        // function parseCBIS() {

                        //     if( (typeof cbis_ajax_vars.cbis_keys[i] == 'undefined') ) {
                        //         console.log('undefined key');
                        //         return;
                        //     }

                        //     $.ajax({
                        //         url: eventmanager.ajaxurl,
                        //         type: 'post',
                        //         data: data
                        //         },
                        //         beforeSend: function() {

                        //         },
                        //         success: function(response) {
                        //             var newPosts = response;
                        //             console.log( response );
                        //             loadingOccasions = false;
                        //             Eventhandling.prototype.dataPopUp(newPosts);
                        //             Eventhandling.prototype.restoreButton(button, storedCss);

                        //             i++;
                        //             parseCBIS();
                        //         }
                        //     })
                        // }

                        if (button.attr('id') === "cbis") {
                            console.log('run cbis');
                            Eventhandling.prototype.parseCBIS(data, button, storedCss);
                        } else {
                            console.log('run xcap or locations');
                            jQuery.post(ajaxurl, data, function(response) {
                            newPosts = response;
                            console.log(newPosts);
                            loadingOccasions = false;
                            Eventhandling.prototype.dataPopUp(newPosts);
                            Eventhandling.prototype.restoreButton(button, storedCss);
                            });
                        }

                    });
                }
            });

            $(document).on('click', '#occasions', function (e) {
                e.preventDefault();
                if (! loadingOccasions) {
                    loadingOccasions = true;
                    var button = $(this);
                    var storedCss = Eventhandling.prototype.collectCssFromButton(button);
                    Eventhandling.prototype.redLoadingButton(button, function() {
                        var data = {
                            'action'    : 'collect_occasions'
                        };

                        jQuery.post(ajaxurl, data, function(response) {
                            console.log(response);
                            loadingOccasions = false;
                            Eventhandling.prototype.restoreButton(button, storedCss);
                        });
                    });
                }
            });

        }.bind(this));
    }

    Eventhandling.prototype.parseCBIS = function(data, button, storedCss) {

        // Show result if theres no api keys left
        if( (typeof cbis_ajax_vars.cbis_keys[i] == 'undefined') ) {
            console.log('undefined key');
            Eventhandling.prototype.dataPopUp(newPosts);
            Eventhandling.prototype.restoreButton(button, storedCss);
            return;
        }

        $.ajax({
            url: eventmanager.ajaxurl,
            type: 'post',
            data: data,
            beforeSend: function() {

            },
            success: function(response) {
                // Update response object
                newPosts.events    += response.events;
                newPosts.locations += response.locations;
                newPosts.contacts  += response.contacts;

                console.log( newPosts );
                loadingOccasions = false;
                // Eventhandling.prototype.dataPopUp(newPosts);
                // Eventhandling.prototype.restoreButton(button, storedCss);

                i++;
                Eventhandling.prototype.parseCBIS(data, button, storedCss);
            }
        })

    };

    Eventhandling.prototype.dataPopUp = function(newData){
        $('#blackOverlay').show();
        var responsePopup = $('#importResponse');
        responsePopup.show(500, function() {
            var eventNumber = responsePopup.find('#event');
            var locationNumber = responsePopup.find('#location');
            var contactNumber = responsePopup.find('#contact');
            var normalTextSize = eventNumber.css('fontSize');
            var bigTextSize = '26px';
            eventNumber.text(newData.events);
            locationNumber.text(newData.locations);
            contactNumber.text(newData.contacts);
            eventNumber.animate({opacity: 1}, long).animate({fontSize: bigTextSize}, short).animate({fontSize: normalTextSize}, short, function() {
                locationNumber.animate({opacity: 1}, long).animate({fontSize: bigTextSize}, short).animate({fontSize: normalTextSize}, short, function() {
                    contactNumber.animate({opacity: 1}, long).animate({fontSize: bigTextSize}, short).animate({fontSize: normalTextSize}, short, function() {
                        var loadingBar = responsePopup.find('#untilReload #meter');
                        loadingBar.animate({width: '100%'}, 7000, function() {
                            location.reload();
                        });
                    });
                });
            });
        });
    };

    Eventhandling.prototype.collectCssFromButton = function (button) {
        return {
            bgColor: button.css('background-color'),
            textColor: button.css('color'),
            borderColor: button.css('border-color'),
            textShadow: button.css('text-shadow'),
            boxShadow: button.css('box-shadow'),
            width: button.css('width'),
            text: button.text()
        };
    };

    Eventhandling.prototype.redLoadingButton = function (button, callback) {
        button.fadeOut(500, function() {
            var texts = [eventmanager.loading + '&nbsp;&nbsp;&nbsp;', eventmanager.loading + '.&nbsp;&nbsp;', eventmanager.loading + '..&nbsp;', eventmanager.loading + '...'];
            button.css('background-color', 'rgb(51, 197, 255)');
            button.css('border-color', 'rgb(0, 164, 230)');
            button.css('color', 'white');
            button.css('text-shadow', '0 -1px 1px rgb(0, 164, 230),1px 0 1px rgb(0, 164, 230),0 1px 1px rgb(0, 164, 230),-1px 0 1px rgb(0, 164, 230)');
            button.css('box-shadow', 'none');
            button.css('width', '85px');
            button.html(texts[0]);
            button.fadeIn(500);

            var counter = 1;
            timerId = setInterval(function()
            {
                if(counter > 3)
                    counter = 0;
                button.html(texts[counter]);
                ++counter;
            }, 500);
            if(callback != undefined)
                callback();
        });
    };

    Eventhandling.prototype.restoreButton = function (button, storedCss) {
        button.fadeOut(500, function() {
            button.css('background-color', storedCss.bgColor);
            button.css('color', storedCss.textColor);
            button.css('border-color', storedCss.borderColor);
            button.css('text-shadow', storedCss.textShadow);
            button.css('box-shadow', storedCss.boxShadow);
            button.css('width', storedCss.width);
            button.text(storedCss.text);
            button.fadeIn(500);
            clearTimeout(timerId);
        });
    };

    return new Eventhandling();

})(jQuery);
