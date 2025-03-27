<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft;

final class PlayableSpecialization {

    public function __construct(
        public readonly int $id,
        public readonly string $name
    ) {}

}