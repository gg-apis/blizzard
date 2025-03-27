<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\PetCollection;

use Countable;

interface PetCollectionSummary extends Countable {
    /**
     * @return list<Pet>
     */
    public function pets() : array;
}