<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\PetCollection;

final class Pet {

    public function __construct(
        public readonly int $id,
        public readonly int $level,
        public readonly PetSpecies $species,
        public readonly PetQuality $quality,
        public readonly PetStats $stats,
    ) {
    }

}
