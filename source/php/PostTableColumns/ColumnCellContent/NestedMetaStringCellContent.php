<?php

namespace EventManager\PostTableColumns\ColumnCellContent;

use EventManager\PostTableColumns\Helpers\GetNestedArrayStringValueRecursiveInterface;
use WpService\Contracts\EscHtml;
use WpService\Contracts\GetPostMeta;
use WpService\Contracts\GetTheID;

class NestedMetaStringCellContent implements ColumnCellContentInterface
{
    /**
     * Class NestedMetaStringCellContent
     *
     * Represents the cell content for a nested meta string column in a post table.
     *
     * @param string $nestedMetaKeys Eg. 'foo.bar.baz' to retrieve $meta[1]['foo']['bar']['baz']
     * @param GetTheID&GetPostMeta&EscHtml $wpService
     */
    public function __construct(
        private string $nestedMetaKeys,
        private GetTheID&GetPostMeta&EscHtml $wpService,
        private GetNestedArrayStringValueRecursiveInterface $getNestedArrayStringValueRecursive
    ) {
    }

    public function getCellContent(): string
    {
        $postId          = $this->wpService->getTheID();
        $cellIdentifiers = explode('.', $this->nestedMetaKeys);
        $metaValueArray  = $this->wpService->getPostMeta($postId, $cellIdentifiers[0], true);


        if (!is_array($metaValueArray) || empty($metaValueArray)) {
            return '';
        }

        array_shift($cellIdentifiers);
        $cellContent = $this
            ->getNestedArrayStringValueRecursive
            ->getNestedArrayStringValueRecursive($cellIdentifiers, $metaValueArray);
        return $this->sanitizeCellContent($cellContent);
    }

    private function sanitizeCellContent($cellContent): string
    {
        return is_string($cellContent) || is_numeric($cellContent)
            ? $this->wpService->escHtml((string)$cellContent)
            : '';
    }
}
