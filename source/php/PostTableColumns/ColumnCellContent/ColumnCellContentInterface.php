<?php

namespace EventManager\PostTableColumns\ColumnCellContent;

interface ColumnCellContentInterface
{
    public function getCellContent(string $cellIdentifier): string;
}
