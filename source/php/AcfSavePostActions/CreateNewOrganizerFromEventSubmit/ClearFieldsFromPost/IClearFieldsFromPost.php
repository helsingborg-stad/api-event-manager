<?php

namespace EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\ClearFieldsFromPost;

interface IClearFieldsFromPost
{
    public function clearFields(int|string $postId): void;
}
