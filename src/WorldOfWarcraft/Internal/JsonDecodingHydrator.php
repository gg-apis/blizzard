<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\Internal;

class JsonDecodingHydrator implements ResourceHydrator {

    public function hydrate(string $body) : object {
        return json_decode($body, flags: JSON_THROW_ON_ERROR);
    }
}