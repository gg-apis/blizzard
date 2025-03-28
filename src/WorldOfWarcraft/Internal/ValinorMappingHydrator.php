<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\Internal;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\TreeMapper;

final class ValinorMappingHydrator implements ResourceHydrator {

    public function __construct(
        private readonly TreeMapper $mapper,
        private readonly ValinorSourceProvider $sourceProvider,
        private readonly string $type,
    ) {}

    public function hydrate(string $body) : object {
        try {
            return $this->mapper->map($this->type, $this->sourceProvider->source($body));
        } catch (MappingError $mappingError) {
            throw $mappingError;
        }
    }
}