<?php

namespace EventManager\NotificationServices;

interface NotificationService
{
    public function send(): void;
    /**
     * @param int[] $userIds
     * @return void
     */
    public function setRecipients(array $userIds): void;
    public function setSubject(string $subject): void;
    public function setMessage(string $message): void;
}
