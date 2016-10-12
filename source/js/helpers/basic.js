var ImportEvents = ImportEvents || {};

jQuery(document).ready(function ($) {
    if($('.acf-field-57ebb807988f8').length)
    {
        //add this class for a button instead of link 'page-title-action'
        $('.acf-field-57ebb807988f8').append('<a class="createContact button" href="http://' + window.location.host + '/wp/wp-admin/post-new.php?post_type=contact&lightbox=true">Create new contact</a>');
    }

    if($('.acf-field-57a9d5f3804e1').length)
    {
        //add this class for a button instead of link 'page-title-action'
        $('.acf-field-57a9d5f3804e1').append('<a class="createContact button" href="http://' + window.location.host + '/wp/wp-admin/post-new.php?post_type=sponsor&lightbox=true">Create new sponsor</a>');
    }

    if($('.acf-field-576117c423a52').length)
    {
        //add this class for a button instead of link 'page-title-action'
        $('.acf-field-576117c423a52').append('<a class="createContact button" href="http://' + window.location.host + '/wp/wp-admin/post-new.php?post_type=location&lightbox=true">Create new location</a>');
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
