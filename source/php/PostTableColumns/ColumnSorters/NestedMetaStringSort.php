<?php

namespace EventManager\PostTableColumns\ColumnSorters;

use EventManager\PostTableColumns\Helpers\GetNestedArrayStringValueRecursiveInterface;
use EventManager\Services\WPService\GetPostMeta;
use EventManager\Services\WPService\GetPosts;
use WP_Query;

class NestedMetaStringSort implements ColumnSortInterface
{
    public function __construct(
        private GetPosts&GetPostMeta $wpService,
        private GetNestedArrayStringValueRecursiveInterface $getNestedArrayStringValueRecursive
    ) {
    }

    public function sort(string $columnIdentifier, WP_Query $query): WP_Query
    {
        $columnIdentifiers             = explode('.', $columnIdentifier);
        $order                         = $query->get('order') === 'asc' ? 'ASC' : 'DESC';
        $postIds                       = $this->wpService->getPosts([
            'meta_key'  => $columnIdentifiers[0],
            'post_type' => 'any',
            'fields'    => 'ids'
        ]);
        $postIdsMappedToInnerMetaValue = $this->getPostIdsMappedToInnerMetaValue($postIds, $columnIdentifiers);

        if ($order === 'ASC') {
            asort($postIdsMappedToInnerMetaValue);
        } else {
            arsort($postIdsMappedToInnerMetaValue);
        }

        $query->set('post__in', array_keys($postIdsMappedToInnerMetaValue));
        $query->set('orderby', 'post__in');

        return $query;
    }

    private function getPostIdsMappedToInnerMetaValue(array $postIds, array $columnIdentifiers): array
    {
        $metaKey                       = $columnIdentifiers[0];
        $columnIdentifiers             = array_slice($columnIdentifiers, 1);
        $postIdsMappedToInnerMetaValue = [];

        foreach ($postIds as $postId) {
            $metaValueArray                         = $this->wpService->getPostMeta($postId, $metaKey, true);
            $innerMetaValue                         = $this->getNestedArrayStringValueRecursive->getNestedArrayStringValueRecursive($columnIdentifiers, $metaValueArray);
            $postIdsMappedToInnerMetaValue[$postId] = $innerMetaValue;
        }

        return $postIdsMappedToInnerMetaValue;
    }
}
