<?php declare(strict_types=1);

namespace GGApis\Blizzard\Oauth;


class AuthorizationParameters {

    public readonly string $grantType;

    public function __construct(
        public readonly string $code,
        public readonly string $state,
        /**
         * @var list<Scope>
         */
        public readonly array $scopes
    ) {
        $this->grantType = 'authorization_code';
    }
}