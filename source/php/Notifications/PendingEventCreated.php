<?php

namespace EventManager\Notifications;

use EventManager\HooksRegistrar\Hookable;
use EventManager\NotificationServices\NotificationService;
use WP_Post;
use WpService\Contracts\__;
use WpService\Contracts\AddAction;
use WpService\Contracts\GetEditPostLink;
use WpService\Contracts\GetUserdata;
use WpService\Contracts\GetUsers;

class PendingEventCreated implements Hookable
{
    public function __construct(
        private NotificationService $notificationSender,
        private GetUsers&AddAction&GetUserdata&GetEditPostLink&__ $wpService
    ) {
    }

    public function addHooks(): void
    {
        $this->wpService->addAction('transition_post_status', [$this, 'onPostCreated'], 10, 3);
        $this->wpService->addAction('save_post', [$this, 'onSavePost'], 10, 3);
    }

    public function onPostCreated(string $newStatus, string $oldStatus, WP_Post $post): void
    {
        if ($post->post_type === 'event' && $newStatus === 'pending') {
            $this->send($post);
        }
    }

    public function onSavePost(int $postId, WP_Post $post, bool $update): void
    {
        if ($post->post_type === 'event' && $post->post_status === 'pending' && !$update) {
            $this->send($post);
        }
    }

    private function send(WP_Post $post): void
    {
        $subject  = $this->wpService->__('Event submission receipt');
        $message  = $this->wpService->__('This is a receipt for your event submission.');
        $message .= PHP_EOL;
        $message .= $this->wpService->__('It will be reviewed by an administrator.');
        $message .= $this->wpService->__('To edit the event before review, please visit the following link: ');
        $message .= $this->wpService->getEditPostLink($post->ID);

        $this->notificationSender->setRecipients([$post->post_author]);
        $this->notificationSender->setSubject($subject);
        $this->notificationSender->setMessage($message);
        $this->notificationSender->send();
    }
}
