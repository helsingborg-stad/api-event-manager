ImportEvents = ImportEvents || {};
ImportEvents.Admin = ImportEvents.Admin || {};

ImportEvents.Admin.AcceptDeny = (function ($) {

    function AcceptDeny() {
        $(function(){
            this.handleEvents();
        }.bind(this));
    }

    /**
     * Accept or deny events.
     * @param  int postStatus 1 = accept, 0 = deny
     * @param  int postId     event object id
     * @return void
     */
    AcceptDeny.prototype.changeAccepted = function(postStatus, postId) {
            $.ajax({
            url: eventmanager.ajaxurl,
            type: 'post',
            data: {
                action    : 'accept_or_deny',
                value     : postStatus,
                postId    : postId
            },
            beforeSend: function(response) {
                var postElement = $('#post-' + postId);
                if (postStatus == 1) {
                    postElement.find('.deny').removeClass('hidden');
                    postElement.find('.accept').addClass('hidden');
                } else if(postStatus == 0) {
                    postElement.find('.deny').addClass('hidden');
                    postElement.find('.accept').removeClass('hidden');
                }
            }
        });
    };

    /**
     * Handle events
     * @return void
     */
    AcceptDeny.prototype.handleEvents = function () {
        $(document).on('click', '.accept', function (e) {
            e.preventDefault();
            var postId = $(e.target).attr('post-id');
            AcceptDeny.prototype.changeAccepted(1, postId);
        }.bind(this));

        $(document).on('click', '.deny', function (e) {
            e.preventDefault();
            var postId = $(e.target).attr('post-id');
            AcceptDeny.prototype.changeAccepted(0, postId);
        }.bind(this));
    };

    return new AcceptDeny();

})(jQuery);
