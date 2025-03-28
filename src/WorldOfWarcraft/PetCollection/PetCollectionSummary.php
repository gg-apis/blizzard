<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\PetCollection;

use Countable;

final class PetCollectionSummary implements Countable {

    public function __construct(
        /** @var list<Pet> $pets */
        public readonly array $pets
    ) {}

    public function count() : int {
        return count($this->pets);
    }
}