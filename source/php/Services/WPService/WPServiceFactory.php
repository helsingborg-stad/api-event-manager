<?php

namespace EventManager\Services\WPService;

use EventManager\Services\WPService\Traits;

class WPServiceFactory
{
    public static function create(): WPService
    {
        return new class implements WPService {
            use Traits\AddAction;
            use Traits\AddFilter;
            use Traits\ApplyFilters;
            use Traits\DeleteTerm;
            use Traits\GetPermalink;
            use Traits\GetPost;
            use Traits\GetPostMeta;
            use Traits\GetPostParent;
            use Traits\GetPosts;
            use Traits\GetTerm;
            use Traits\GetTermMeta;
            use Traits\GetTerms;
            use Traits\GetThePostThumbnailUrl;
            use Traits\GetTheTitle;
            use Traits\IsWPError;
            use Traits\RegisterPostType;
            use Traits\RegisterTaxonomy;
            use Traits\RemoveMenuPage;
            use Traits\RemoveSubMenuPage;
            use Traits\TermExists;
            use Traits\WPGetPostTerms;
            use Traits\WPInsertTerm;
            use Traits\WPSetPostTerms;
        };
    }
}
