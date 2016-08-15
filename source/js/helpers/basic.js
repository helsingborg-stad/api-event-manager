var ImportEvents = ImportEvents || {};

jQuery(document).ready(function ($) {
    if($('#acf-field_576116fd23a4f').length)
    {
        //add this class for a button instead of link 'page-title-action'
        $('#acf-field_576116fd23a4f').append('<a class="createContact button button-primary" href="http://' + window.location.host + '/wp/wp-admin/post-new.php?post_type=contact&lightbox=true">Create new contact</a>');
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
