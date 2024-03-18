<?php

namespace EventManager\TableColumns;

interface TableColumnInterface
{
    public function getHeader(): string;
    public function getName(): string;
    public function getCellContent(): string;
}
