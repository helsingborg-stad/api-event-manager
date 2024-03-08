<?php

namespace EventManager\Services\WPService;

interface WPService extends
    AddAction,
    AddFilter,
    ApplyFilters,
    DeleteTerm,
    GetPermalink,
    GetPost,
    GetPostMeta,
    GetPostParent,
    GetPosts,
    GetTerm,
    GetTermMeta,
    GetTerms,
    GetThePostThumbnailUrl,
    GetTheTitle,
    IsWPError,
    RegisterPostType,
    RegisterTaxonomy,
    RemoveMenuPage,
    RemoveSubMenuPage,
    TermExists,
    WPGetPostTerms,
    WPInsertTerm,
    WPSetPostTerms
{
}
