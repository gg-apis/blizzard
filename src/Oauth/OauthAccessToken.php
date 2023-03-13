<?php declare(strict_types=1);

namespace GGApis\Blizzard\Oauth;

final class OauthAccessToken {

    public function __construct(
        public readonly string $accessToken,
        public readonly string $tokenType,
        public readonly int $expiresIn,
        /** @var list<Scope> */
        public readonly array $scopes
    ) {}

}
