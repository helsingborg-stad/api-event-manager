jQuery(document).ready(function ($) {

    if(pagenow == 'edit-event' || pagenow == 'edit-location')
    {
        $('#wpwrap').append('<div id="blackOverlay"></div>');
        $('.wrap').append('\
            <div id="importResponse">\
                <div><h3>'+ eventmanager.new_data_imported +'</h3></div>\
                <div class="inline"><p><strong>'+ eventmanager.events +'</strong></p></div><div class="inline"><p><strong>'+ eventmanager.locations +'</strong></p></div><div class="inline"><p><strong>'+ eventmanager.contacts +'</strong></p></div>\
                <div class="inline"><p id="event">0</p></div><div class="inline"><p id="location">0</p></div><div class="inline"><p id="contact">0</p></div>\
                <div id="untilReload"><div id="meter"></div><p>'+ eventmanager.time_until_reload +'</p></div>\
            </div>\
        ');
    }
});
