<?php declare(strict_types=1);

namespace GGApis\Blizzard\Http;

final class BearerTokenHeader {

    private function __construct(
        private readonly string $token
    ) {}

    public static function fromToken(string $token) : self {
        return new self($token);
    }

    public function toString() : string {
        return sprintf('Bearer %s', $this->token);
    }

}