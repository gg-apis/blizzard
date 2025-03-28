<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\MythicKeystone;

use GGApis\Blizzard\WorldOfWarcraft\CharacterIdentifier;

class MythicKeystoneCharacterSeasonDetails {

    public function __construct(
        public readonly CharacterIdentifier $character,
        public readonly int $seasonId,
        /** @var list<MythicKeystoneRun> */
        public readonly array $bestRuns,
        public readonly MythicKeystoneRating $rating
    ) {}

}