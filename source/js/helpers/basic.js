var ImportEvents = ImportEvents || {};

jQuery(document).ready(function ($) {
    if($('.acf-field-57ebb807988f8').length)
    {
        $('.acf-field-57ebb807988f8').append('<a class="createContact button" href="http://' + window.location.host + '/wp/wp-admin/post-new.php?post_type=contact&lightbox=true">' + eventmanager.new_contact + '</a>');
    }

    if($('.acf-field-57a9d5f3804e1').length)
    {
        $('.acf-field-57a9d5f3804e1').append('<a class="createContact button" href="http://' + window.location.host + '/wp/wp-admin/post-new.php?post_type=sponsor&lightbox=true">' + eventmanager.new_sponsor + '</a>');
    }

    if($('.acf-field-576117c423a52').length)
    {
        $('.acf-field-576117c423a52').append('<a class="createContact button" href="http://' + window.location.host + '/wp/wp-admin/post-new.php?post_type=location&lightbox=true">' + eventmanager.new_location + '</a>');
    }

    if($('.acf-field-57c7ed92054e6').length)
    {
        $('.acf-field-57c7ed92054e6').append('<a class="createContact button" href="http://' + window.location.host + '/wp/wp-admin/post-new.php?post_type=membership-card&lightbox=true">' + eventmanager.new_card + '</a>');
    }

    $('.openContact').click(function(event) {
        event.preventDefault();
        ImportEvents.Prompt.Modal.open($(this).attr('href'));
    });

    $('.createContact').click(function(event) {
        var parentId = $('#post_ID').val();
        event.preventDefault();
        ImportEvents.Prompt.Modal.open($(this).attr('href'), parentId);
    });
});
