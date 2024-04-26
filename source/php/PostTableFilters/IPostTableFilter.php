<?php

namespace EventManager\PostTableFilters;

interface IPostTableFilter
{
    public function outputFilterMarkup(string $postType): void;
}
