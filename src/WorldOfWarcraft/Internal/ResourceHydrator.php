<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\Internal;

interface ResourceHydrator {

    public function hydrate(string $body) : object;

}