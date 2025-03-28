<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\PetCollection;

use Countable;

final class PetCollectionSummary implements Countable {

    public function __construct(
        /** @var list<Pet> $pets */
        public readonly array $pets
    ) {}

    public function isPetCollected(string $name) : bool {
        foreach ($this->pets as $pet) {
            if ($name === $pet->species->name) {
                return true;
            }
        }

        return false;
    }

    public function count() : int {
        return count($this->pets);
    }
}