<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\MythicKeystone;

use GGApis\Blizzard\WorldOfWarcraft\PlayableRace;
use GGApis\Blizzard\WorldOfWarcraft\PlayableSpecialization;

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