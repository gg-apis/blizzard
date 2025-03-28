<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\CharacterProfile;

use GGApis\Blizzard\Oauth\ClientAccessToken;
use GGApis\Blizzard\RegionAndLocale;
use GGApis\Blizzard\WorldOfWarcraft\UserProfile\Character;

interface CharacterProfileApi {

    public function fetchCharacterStatus(ClientAccessToken $token, Character $character, RegionAndLocale $regionAndLocale = null) : CharacterStatus;

}
