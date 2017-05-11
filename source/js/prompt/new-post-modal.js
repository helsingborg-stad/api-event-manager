ImportEvents = ImportEvents || {};
ImportEvents.Admin = ImportEvents.Admin || {};

ImportEvents.Admin.NewPostModal = (function ($) {

    function NewPostModal() {
        $(function(){

            this.createTrigger('location', '.acf-field-576117c423a52');
            this.createTrigger('contact', '.acf-field-57ebb807988f8');
            this.createTrigger('sponsor', '.acf-field-57a9d5f3804e1');
            this.createTrigger('membership-card', '.acf-field-57c7ed92054e6');
            this.createTrigger('membership-card', '.acf-field-581847f9642dc');

            this.bindLaunchModal();

        }.bind(this));
    }

    /**
     * Create button to trigger new post modal
     * @param  string   posttype to create
     * @param  string   triggering class or id
     * @return void
     */
    NewPostModal.prototype.createTrigger = function(postType, triggerClass) {
        if ($(triggerClass).length) {
            if (typeof eventmanager['new_' + postType] !== 'undefined') {
               $(triggerClass).append('<a class="createNewPost button" href="' + eventmanager.adminurl + 'post-new.php?post_type=' + postType+ '&lightbox=true">' + eventmanager['new_' + postType] + '</a>');
            }
        }
    };

    /**
     * Hook on trigger button to launch modal
     * @return void
     */
    NewPostModal.prototype.bindLaunchModal = function() {
        $(document).on('click','.createNewPost', function(e) {
            e.preventDefault();
            ImportEvents.Prompt.Modal.open($(this).attr('href'), $('#post_ID').val());
        });
    };

    return new NewPostModal();

})(jQuery);
