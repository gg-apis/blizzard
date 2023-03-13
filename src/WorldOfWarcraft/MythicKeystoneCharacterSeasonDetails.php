<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft;

class MythicKeystoneCharacterSeasonDetails {

    public function __construct(
        public readonly Character $character,
        public readonly int $seasonId,
        /** @var list<MythicKeystoneRun> */
        public readonly array $bestRuns,
        public readonly MythicKeystoneRating $rating
    ) {}

}