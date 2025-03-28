<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\UserProfile;

use GGApis\Blizzard\WorldOfWarcraft\CharacterIdentifier;
use GGApis\Blizzard\WorldOfWarcraft\PlayableRace;

final class Character implements CharacterIdentifier {

    public function __construct(
        public readonly int           $id,
        public readonly string        $name,
        public readonly Realm         $realm,
        public readonly PlayableRace  $race,
        public readonly PlayableClass $class,
        public readonly int           $level,
        public readonly Gender        $gender,
        public readonly Faction       $faction
    ) {}

    public function getLowercaseName() : string {
        return strtolower($this->name);
    }

    public function getRealmSlug() : string {
        return $this->realm->slug;
    }
}