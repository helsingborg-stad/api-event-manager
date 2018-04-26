var ImportEvents = ImportEvents || {};

ImportEvents = ImportEvents || {};
ImportEvents.Parser = ImportEvents.Parser || {};

ImportEvents.Parser.Eventhandling = (function ($) {

    var newPosts = {
        events: 0,
        locations: 0,
        organizers: 0
    };

    var data = {
        action: 'import_events',
        value: '',
        api_keys: '',
        cron: false
    };

    var short = 200;
    var long = 400;
    var timerId = null;
    var loadingOccasions = false;
    var i = 0;
    var j = 0;

    function Eventhandling() {
        $(document).on('click', '#transticket', function (e) {
            e.preventDefault();
            data.value = 'transticket';

            if (!loadingOccasions) {
                loadingOccasions = true;

                var button = $(e.target).closest('#transticket');
                var storedCss = this.collectCssFromButton(button);

                this.redLoadingButton(button, function() {
                    this.parseEvents(data, button, storedCss);
                    return;
                }.bind(this));
            }
        }.bind(this));

        $(document).on('click', '#xcap', function (e) {
            e.preventDefault();
            data.value = 'xcap';

            if (!loadingOccasions) {
                loadingOccasions = true;

                var button = $(e.target).closest('#xcap');
                var storedCss = this.collectCssFromButton(button);

                this.redLoadingButton(button, function() {
                    this.parseEvents(data, button, storedCss);
                    return;
                }.bind(this));
            }
        }.bind(this));

        $(document).on('click', '#cbis', function (e) {
            e.preventDefault();
            data.value = 'cbis';

            if (!loadingOccasions) {
                loadingOccasions = true;

                var button = $(e.target).closest('#cbis');
                var storedCss = this.collectCssFromButton(button);

                this.redLoadingButton(button, function() {
                    this.parseEvents(data, button, storedCss);
                    return;
                }.bind(this));
            }
        }.bind(this));

        $(document).on('click', '#cbislocation', function (e) {
            e.preventDefault();
            data.value = 'cbislocation';

            if (!loadingOccasions) {
                loadingOccasions = true;

                var button = $(e.target).closest('#cbislocation');
                var storedCss = this.collectCssFromButton(button);

                this.redLoadingButton(button, function() {
                    this.parseCbislocation(data, button, storedCss);
                    return;
                }.bind(this));
            }
        }.bind(this));

        $(document).on('click', '#occasions', function (e) {
            e.preventDefault();

            if (!loadingOccasions) {
                loadingOccasions = true;

                var button = $(e.target).closest('#occasions');
                var storedCss = this.collectCssFromButton(button);

                this.redLoadingButton(button, function() {
                    var data = {
                        action: 'collect_occasions'
                    };

                    jQuery.post(ajaxurl, data, function(response) {
                        loadingOccasions = false;
                        Eventhandling.prototype.restoreButton(button, storedCss);
                    });
                }.bind(this));
            }
        }.bind(this));

        this.importModal();
    }

    Eventhandling.prototype.importModal = function() {
        if (['edit-event', 'edit-location'].indexOf(pagenow) == -1) {
            return;
        }

        $(document).ready(function () {
            $('#wpwrap').append('<div id="blackOverlay"></div>');
            $('.wrap').append('\
                <div id="importResponse">\
                    <div><h3>'+ eventmanager.new_data_imported +'</h3></div>\
                    <div class="inline"><p><strong>'+ eventmanager.events +'</strong></p></div><div class="inline"><p><strong>'+ eventmanager.locations +'</strong></p></div><div class="inline"><p><strong>'+ eventmanager.organizers +'</strong></p></div>\
                    <div class="inline"><p id="event">0</p></div><div class="inline"><p id="location">0</p></div><div class="inline"><p id="organizer">0</p></div>\
                    <div id="untilReload"><div id="meter"></div><p>'+ eventmanager.time_until_reload +'</p></div>\
                </div>\
            ');
        })
    };

    /**
     * Parse CBIS, XCAP, TransTicket events, loop through each API key
     * @param  {array}   data        Data to parse
     * @param  {element} button      Clicked button
     * @param  {object}  storedCss   Default button  css
     * @return {void}
     */
    Eventhandling.prototype.parseEvents = function(data, button, storedCss) {
        if (data.value === 'cbis') {
            data.api_keys = cbis_ajax_vars.cbis_keys[i];
        } else if (data.value === 'xcap') {
            data.api_keys = xcap_ajax_vars.xcap_keys[i];
        } else if (data.value === 'transticket') {
            data.api_keys = transticket_ajax_vars.transticket_keys[i];
        }

        // Show result if there's no API keys left to parse
        if (typeof data.api_keys === 'undefined') {
            loadingOccasions = false;

            // Show data pop up if function is not called with cron
            if (!data.cron) {
                this.dataPopUp(newPosts);
                this.restoreButton(button, storedCss);
            }

            return;
        }

        $.ajax({
            url: eventmanager.ajaxurl,
            type: 'post',
            data: data,
            success: function(response) {
                // Update response object
                newPosts.events      += response.events;
                newPosts.locations   += response.locations;
                newPosts.organizers  += response.organizers;

                // Run function again
                i++;
                ImportEvents.Parser.Eventhandling.parseEvents(data, button, storedCss);
            }
        });
    };

    /**
     * Parse CBIS locations, loop through each API key and its categories
     * @param  {object}  data      Data to parse
     * @param  {element} button    Button element
     * @param  {object}  storedCss Button default css
     * @return {void}
     */
    Eventhandling.prototype.parseCbislocation = function(data, button, storedCss) {
        j = 0;

        // Show import result when done
        if( (typeof cbis_ajax_vars.cbis_keys[i] === 'undefined') ) {
            loadingOccasions = false;

            // Show data pop up if function is not called with cron
            if (!data.cron) {
                this.dataPopUp(newPosts);
                this.restoreButton(button, storedCss);
            }

            return;
        }

        data.api_keys = cbis_ajax_vars.cbis_keys[i];

        // Wait for callback and run this function again until there's no API keys left to parse
        $.when(this.parseLocations(data)).then(function() {
            i++;
            this.parseCbislocation(data, button, storedCss) ;
        }.bind(this));
    };

    /**
     * Parse each location category ID
     * @param  {object} data Data to parse
     * @return {object}      Deferred object
     */
    Eventhandling.prototype.parseLocations = function(data){
        var deferredObject = $.Deferred();

        Eventhandling.prototype.parse = function() {
            // Return when done
            if (typeof data.api_keys.cbis_locations[j] === 'undefined') {
                deferredObject.resolve();
                return;
            }

            data.cbis_location = data.api_keys.cbis_locations[j];

            // Wait for Ajax callback and run this function again until there's no categories left
            $.when(this.parseLocationCategory(data)).then(function() {
                j++;
                this.parse(data);
            }.bind(this));
        };

        this.parse();

        return deferredObject.promise();
    };

    /**
     * Call ajax with category ID
     * @param  {object} data Ajax data
     * @return {void}
     */
    Eventhandling.prototype.parseLocationCategory = function(data){
        return $.ajax({
            url: eventmanager.ajaxurl,
            type: 'post',
            data: data,
            success: function(response) {
                // Update response object
                newPosts.events      += response.events;
                newPosts.locations   += response.locations;
                newPosts.organizers  += response.organizers;
            }
        });
    };

    /**
     * Show data popup
     * @param  {object} newData Data to display
     * @return {void}
     */
    Eventhandling.prototype.dataPopUp = function(newData){
        $('#blackOverlay').show();
        var responsePopup = $('#importResponse');

        responsePopup.show(500, function() {
            var eventNumber     = responsePopup.find('#event');
            var locationNumber  = responsePopup.find('#location');
            var organizerNumber = responsePopup.find('#organizer');
            var normalTextSize  = eventNumber.css('fontSize');
            var bigTextSize     = '26px';

            eventNumber.text(newData.events);
            locationNumber.text(newData.locations);
            organizerNumber.text(newData.organizers);

            eventNumber.animate({opacity: 1}, long).animate({fontSize: bigTextSize}, short).animate({fontSize: normalTextSize}, short, function() {
                locationNumber.animate({opacity: 1}, long).animate({fontSize: bigTextSize}, short).animate({fontSize: normalTextSize}, short, function() {
                    organizerNumber.animate({opacity: 1}, long).animate({fontSize: bigTextSize}, short).animate({fontSize: normalTextSize}, short, function() {
                        var loadingBar = responsePopup.find('#untilReload #meter');
                        loadingBar.animate({width: '100%'}, 7000, function() {
                            location.reload();
                        });
                    });
                });
            });
        });
    };

    /**
     * Collects a object with css params for a button
     * @param  {element} button The button
     * @return {object}         The button style
     */
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

    /**
     * Transforms button style to red loading button
     * @param  {element}   button    The button to trasnform
     * @param  {Function}  callback  Callback function
     * @return {void}
     */
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
            timerId = setInterval(function() {
                if (counter > 3) {
                    counter = 0;
                }

                button.html(texts[counter]);
                ++counter;
            }, 500);

            if (callback !== undefined)
                callback();
        });
    };

    /**
     * Restores a button to its default state
     * @param  {element} button    The button
     * @param  {object}  storedCss The default css
     * @return {void}
     */
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
