<?php

namespace EventManager\Notifications;

interface NotificationSenderInterface
{
    public function send(NotificationInterface $notification): void;
}
