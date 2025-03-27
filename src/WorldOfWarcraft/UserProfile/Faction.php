<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\UserProfile;

final class Faction {

    public function __construct(
        public readonly string $type,
        public readonly string $name
    ) {}

}