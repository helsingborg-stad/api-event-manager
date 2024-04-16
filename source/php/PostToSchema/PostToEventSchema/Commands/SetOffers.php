<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use Spatie\SchemaOrg\BaseType;
use WP_Post;

class SetOffers implements CommandInterface
{
    public function __construct(private BaseType $schema, private array $meta)
    {
    }

    public function execute(): void
    {
        $priceList = $this->meta['pricesList'] ?? [];

        if (!empty($priceList)) {
            $this->schema->offers(array_map(function ($price) {
                return [
                    '@type'         => 'Offer',
                    'priceCurrency' => 'SEK',
                    'name'          => $price['priceLabel'] ?? '',
                    'price'         => $price['price'] ?? '',
                ];
            }, $priceList));
        }
    }
}
