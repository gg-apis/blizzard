<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\UserProfile;

final class Account {

    public function __construct(
        public readonly int $id,
        /** @var list<Character> */
        public readonly array $characters
    ) {}

}