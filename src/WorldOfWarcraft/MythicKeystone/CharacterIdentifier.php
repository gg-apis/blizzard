<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\MythicKeystone;

interface CharacterIdentifier {

    public function getLowercaseName() : string;

    public function getRealmSlug() : string;

}
