<?php declare(strict_types=1);

namespace GGApis\Blizzard;

final class BlizzardError {

    public function __construct(
        public readonly int $code,
        public readonly string $type,
        public readonly string $detail
    ) {}

}