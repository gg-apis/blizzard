<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft;

final class RgbaColor {

    public readonly float $a;

    public function __construct(
        public readonly int $r,
        public readonly int $g,
        public readonly int $b,
        int|float $a
    ) {
        $this->a = (float) $a;
    }

}
