<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\MythicKeystone;

use GGApis\Blizzard\Oauth\ClientAccessToken;
use GGApis\Blizzard\RegionAndLocale;
use GGApis\Blizzard\WorldOfWarcraft\CharacterIdentifier;

interface MythicKeystoneCharacterApi {

    public function fetchMythicKeystoneCharacterProfile(
        ClientAccessToken $token,
        CharacterIdentifier $character,
        RegionAndLocale $regionAndLocale = null
    ) : MythicKeystoneCharacterProfile;

    public function fetchMythicKeystoneCharacterSeasonDetails(
        ClientAccessToken $token,
        CharacterIdentifier $character,
        int $seasonId,
        RegionAndLocale $regionAndLocale = null
    ) : MythicKeystoneCharacterSeasonDetails;

}
