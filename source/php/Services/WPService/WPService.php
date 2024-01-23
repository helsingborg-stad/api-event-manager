<?php

namespace EventManager\Services\WPService;

interface WPService extends
    GetPostMeta,
    GetPostParent,
    GetPosts,
    GetThePostThumbnailUrl,
    GetPermalink,
    GetTerm,
    GetTermMeta,
    GetTheTitle,
    AddAction,
    AddFilter,
    RegisterPostType,
    RegisterTaxonomy,
    RemoveMenuPage,
    RemoveSubMenuPage
{
}
