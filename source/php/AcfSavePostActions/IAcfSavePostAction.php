<?php

namespace EventManager\AcfSavepostActions;

interface IAcfSavePostAction
{
    public function savePost(int $postId): void;
}
