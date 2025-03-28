<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\PetCollection;

final class PetStats {

    public function __construct(
        public readonly int $breedId,
        public readonly int $health,
        public readonly int $power,
        public readonly int $speed,
    ) {}

}