<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft;

class MythicKeystoneRating {

    public function __construct(
        public readonly float $rating,
        public readonly RgbaColor $color
    ) {}

}