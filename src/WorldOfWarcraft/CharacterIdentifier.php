<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft;

interface CharacterIdentifier {

    public function getLowercaseName() : string;

    public function getRealmSlug() : string;

}
