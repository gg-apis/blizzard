<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft;

class MythicKeystoneAffix {

    public function __construct(
        public readonly int $id,
        public readonly string $name
    ) {}

}