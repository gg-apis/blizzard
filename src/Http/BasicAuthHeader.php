<?php declare(strict_types=1);

namespace GGApis\Blizzard\Http;

final class BasicAuthHeader {

    private function __construct(
        private readonly string $user,
        private readonly string $password
    ) {}

    public static function fromUserInfo(string $user, string $password) : self {
        return new self($user, $password);
    }

    public function toString() : string {
        $value = base64_encode(
            sprintf('%s:%s', $this->user, $this->password)
        );
        return sprintf('Basic %s', $value);
    }

}
