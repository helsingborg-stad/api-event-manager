<?php

namespace EventManager\PostToSchema;

use EventManager\Services\WPService\GetPostMeta;
use EventManager\Services\WPService\GetThePostThumbnailUrl;
use EventManager\Services\WPService\GetPostTerms;
use EventManager\Services\WPService\GetTermMeta;
use EventManager\Services\WPService\GetTerm;
use EventManager\Services\WPService\GetPostParent;
use EventManager\Services\WPService\GetPosts;

interface EventBuilderWpServiceInterface extends
    GetPostMeta,
    GetThePostThumbnailUrl,
    GetPostTerms,
    GetTermMeta,
    GetTerm,
    GetPostParent,
    GetPosts
{
}
