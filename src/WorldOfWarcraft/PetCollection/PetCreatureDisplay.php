<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\PetCollection;

final class PetCreatureDisplay {
     public function __construct(
         public readonly int $id,
     ) {}

}