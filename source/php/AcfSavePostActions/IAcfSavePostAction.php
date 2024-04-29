<?php

namespace EventManager\AcfSavePostActions;

interface IAcfSavePostAction
{
    public function savePost(int|string $postId): void;
}
