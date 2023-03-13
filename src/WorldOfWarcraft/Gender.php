<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft;

final class Gender {

    public function __construct(
        public readonly string $type,
        public readonly string $name
    ) {}

}
