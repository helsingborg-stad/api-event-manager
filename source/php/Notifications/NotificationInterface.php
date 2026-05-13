<?php

namespace EventManager\Notifications;

interface NotificationInterface
{
    public function getSubject(): string;
    public function getMessage(): string;
    public function getRecipients(): array;
}
