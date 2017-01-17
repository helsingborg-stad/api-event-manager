var ImportEvents = ImportEvents || {};

ImportEvents = ImportEvents || {};
ImportEvents.Parser = ImportEvents.Parser || {};

ImportEvents.Parser.Eventhandling = (function ($) {

    var newPosts            = {events:0,locations:0,contacts:0};
    var data                = {action:'import_events', value:'', api_keys:'', cron:false};
    var short               = 200;
    var long                = 400;
    var timerId             = null;
    var loadingOccasions    = false;
    var i                   = 0;
    var j                   = 0;

    function Eventhandling() {
        $(function() {

            $(document).on('click', '#xcap', function (e) {
                e.preventDefault();
                data.value = 'xcap';
                console.log('Parse XCAP');
                if (! loadingOccasions) {
                    loadingOccasions = true;
                    var button = $(this);
                    var storedCss = Eventhandling.prototype.collectCssFromButton(button);
                    Eventhandling.prototype.redLoadingButton(button, function() {
                        Eventhandling.prototype.parseEvents(data, button, storedCss);
                        return;
                    });
                }
            });

            $(document).on('click', '#cbis', function (e) {
                e.preventDefault();
                data.value = 'cbis';

                if (! loadingOccasions) {
                    loadingOccasions = true;
                    var button = $(this);
                    var storedCss = Eventhandling.prototype.collectCssFromButton(button);
                    Eventhandling.prototype.redLoadingButton(button, function() {
                        Eventhandling.prototype.parseEvents(data, button, storedCss);
                        return;
                    });
                }
            });

            $(document).on('click', '#cbislocation', function (e) {
                e.preventDefault();

                if (! loadingOccasions) {
                    loadingOccasions = true;
                    var button = $(this);
                    var storedCss = Eventhandling.prototype.collectCssFromButton(button);
                    Eventhandling.prototype.redLoadingButton(button, function() {
                        Eventhandling.prototype.parseCbislocation(data, button, storedCss);
                        return;
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

    // Parse CBIS & XCAP events, loop through each API key
    Eventhandling.prototype.parseEvents = function(data, button, storedCss) {
        if (data.value === 'cbis') {
            data.api_keys = cbis_ajax_vars.cbis_keys[i];
        } else if (data.value === 'xcap') {
            data.api_keys = xcap_ajax_vars.xcap_keys[i];
        }

        // Show result if there's no API keys left to parse
        if( (typeof data.api_keys == 'undefined') ) {
            loadingOccasions = false;
            // Show data pop up if function is not called with cron
            if (! data.cron) {
                Eventhandling.prototype.dataPopUp(newPosts);
                Eventhandling.prototype.restoreButton(button, storedCss);
            }
            return;
        }

        $.ajax({
            url: eventmanager.ajaxurl,
            type: 'post',
            data: data,
            success: function(response) {
                // Update response object
                newPosts.events    += response.events;
                newPosts.locations += response.locations;
                newPosts.contacts  += response.contacts;
                // Run function again
                i++;
                Eventhandling.prototype.parseEvents(data, button, storedCss);
            }
        })
    };

    // Parse CBIS locations, loop through each API key and its categories
    Eventhandling.prototype.parseCbislocation = function(data, button, storedCss) {
        j = 0;

        // Show import result when done
        if( (typeof cbis_ajax_vars.cbis_keys[i] == 'undefined') ) {
            loadingOccasions = false;
            // Show data pop up if function is not called with cron
            if (! data.cron) {
                Eventhandling.prototype.dataPopUp(newPosts);
                Eventhandling.prototype.restoreButton(button, storedCss);
            }
            return;
        }

        data.api_keys = cbis_ajax_vars.cbis_keys[i];

        // Wait for callback and run this function again until there's no API keys left to parse
        $.when(Eventhandling.prototype.parseLocations(data)).then(function() {
            i++;
            Eventhandling.prototype.parseCbislocation(data, button, storedCss) ;
        });

    };

    // Parse each location category ID
    Eventhandling.prototype.parseLocations = function(data){
        var deferredObject = $.Deferred();

        Eventhandling.prototype.parse = function() {
            // Return when done
            if( (typeof data.api_keys.cbis_locations[j] == 'undefined') ) {
                deferredObject.resolve();
                return;
            }

            data.cbis_location = data.api_keys.cbis_locations[j];
            // Wait for Ajax callback and run this function again until there's no categories left
            $.when(Eventhandling.prototype.parseLocationCategory(data)).then(function() {
                j++;
                Eventhandling.prototype.parse(data);
            });
        };

        Eventhandling.prototype.parse();

        return deferredObject.promise();
    }

    // Call ajax with category ID
    Eventhandling.prototype.parseLocationCategory = function(data){
        return $.ajax({
            url: eventmanager.ajaxurl,
            type: 'post',
            data: data,
            success: function(response) {
                // Update response object
                newPosts.events    += response.events;
                newPosts.locations += response.locations;
                newPosts.contacts  += response.contacts;
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
