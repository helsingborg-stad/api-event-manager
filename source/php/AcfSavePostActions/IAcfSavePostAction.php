<?php

namespace EventManager\AcfSavePostActions;

interface IAcfSavePostAction
{
    public function savePost(int $postId): void;
}
