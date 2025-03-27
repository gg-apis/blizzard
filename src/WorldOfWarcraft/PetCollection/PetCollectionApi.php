<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\PetCollection;

use GGApis\Blizzard\Oauth\OauthAccessToken;
use GGApis\Blizzard\RegionAndLocale;

interface PetCollectionApi {

    public function fetchPetCollectionSummary(OauthAccessToken $accessToken, RegionAndLocale $regionAndLocale = null);

}