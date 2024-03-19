<?php

namespace EventManager\PostTableColumns\ColumnCellContent;

use EventManager\PostTableColumns\Helpers\GetNestedArrayStringValueRecursiveInterface;
use EventManager\Services\WPService\GetPostMeta;
use EventManager\Services\WPService\GetTheId;

class NestedMetaStringCellContent implements ColumnCellContentInterface
{
    public function __construct(
        private GetTheId&GetPostMeta $wpService,
        private GetNestedArrayStringValueRecursiveInterface $getNestedArrayStringValueRecursive
    ) {
    }

    public function getCellContent(string $cellIdentifier): string
    {
        $postId          = $this->wpService->getTheId();
        $cellIdentifiers = explode('.', $cellIdentifier);
        $metaValueArray  = $this->wpService->getPostMeta($postId, $cellIdentifiers[0], true);


        if (!is_array($metaValueArray) || empty($metaValueArray)) {
            return '';
        }

        array_shift($cellIdentifiers);
        $cellContent = $this->getNestedArrayStringValueRecursive->getNestedArrayStringValueRecursive($cellIdentifiers, $metaValueArray);
        return $this->sanitizeCellContent($cellContent);
    }

    private function sanitizeCellContent($cellContent): string
    {
        return is_string($cellContent) || is_numeric($cellContent) ? (string)$cellContent : '';
    }
}
