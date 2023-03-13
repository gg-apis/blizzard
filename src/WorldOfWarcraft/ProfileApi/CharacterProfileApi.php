<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\ProfileApi;

use Cspray\AnnotatedContainer\Attribute\Service;
use GGApis\Blizzard\Oauth\ClientAccessToken;
use GGApis\Blizzard\Region;
use GGApis\Blizzard\WorldOfWarcraft\Character;
use GGApis\Blizzard\WorldOfWarcraft\CharacterStatus;

#[Service]
interface CharacterProfileApi {

    public function fetchCharacterStatus(ClientAccessToken $token, Character $character, Region $region = null) : CharacterStatus;

}
