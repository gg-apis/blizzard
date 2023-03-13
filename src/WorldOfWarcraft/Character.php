<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft;

final class Character {

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

}