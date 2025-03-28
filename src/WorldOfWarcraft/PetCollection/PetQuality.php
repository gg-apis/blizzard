<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\PetCollection;

class PetQuality {
    public function __construct(
        public readonly string $type,
        public readonly string $name,
    ) {}
}