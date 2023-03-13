<?php declare(strict_types=1);

namespace GGApis\Blizzard\Oauth;

final class ClientAccessToken {

    public function __construct(
        public readonly string $accessToken,
        public readonly string $tokenType,
        public readonly int $expiresIn,
        public readonly string $sub
    ) {}

}