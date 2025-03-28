<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\UserProfile;

final class Gender {

    public function __construct(
        public readonly string $type,
        public readonly string $name
    ) {}

}
