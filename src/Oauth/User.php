<?php declare(strict_types=1);

namespace GGApis\Blizzard\Oauth;

class User {
    public function __construct(
        public readonly int $sub,
        public readonly int $id,
        public readonly string $battletag
    ) {}
}