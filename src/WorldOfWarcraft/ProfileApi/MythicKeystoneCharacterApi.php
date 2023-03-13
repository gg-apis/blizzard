<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\ProfileApi;

use Cspray\AnnotatedContainer\Attribute\Service;
use GGApis\Blizzard\Oauth\ClientAccessToken;
use GGApis\Blizzard\RegionAndLocale;
use GGApis\Blizzard\WorldOfWarcraft\Character;
use GGApis\Blizzard\WorldOfWarcraft\MythicKeystoneCharacterProfile;
use GGApis\Blizzard\WorldOfWarcraft\MythicKeystoneCharacterSeasonDetails;

#[Service]
interface MythicKeystoneCharacterApi {

    public function fetchMythicKeystoneCharacterProfile(
        ClientAccessToken $token,
        Character $character,
        RegionAndLocale $regionAndLocale = null
    ) : MythicKeystoneCharacterProfile;

    public function fetchMythicKeystoneCharacterSeasonDetails(
        ClientAccessToken $token,
        Character $character,
        int $seasonId,
        RegionAndLocale $regionAndLocale = null
    ) : MythicKeystoneCharacterSeasonDetails;

}
