<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\UserProfile;

final class UserProfile {

    public function __construct(
        public readonly int $id,
        /** @var list<Account> */
        public readonly array $accounts
    ) {}

}
