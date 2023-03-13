<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft;

final class Realm {

    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $slug,
    ) {}

}
