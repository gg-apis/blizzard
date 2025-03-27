<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\MythicKeystone;

final class MythicKeystoneCharacterProfile {

    public function __construct(
        public readonly CharacterIdentifier $character,
        public readonly int $currentPeriodId,
        /** @var list<int> */
        public readonly array $seasonIds,
        public readonly ?MythicKeystoneRating $rating
    ) {}

}
