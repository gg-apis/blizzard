<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft;

class MythicKeystonePartyMember {

    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly int $realmId,
        public readonly PlayableSpecialization $specialization,
        public readonly PlayableRace $race,
        public readonly int $equippedItemLevel
    ) {}

}