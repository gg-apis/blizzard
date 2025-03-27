<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\MythicKeystone;

class MythicKeystoneRun {

    public function __construct(
        public readonly \DateTimeInterface $completedAt,
        public readonly \DateInterval $duration,
        public readonly int $keystoneLevel,
        /** @var list<MythicKeystoneAffix> */
        public readonly array $affixes,
        /** @var list<MythicKeystonePartyMember> */
        public readonly array $members,
        public readonly MythicKeystoneDungeon $dungeon,
        public readonly bool $isCompletedWithinTime,
        public readonly MythicKeystoneRating $rating,
        public readonly MythicKeystoneRating $mapRating
    ) {}

}