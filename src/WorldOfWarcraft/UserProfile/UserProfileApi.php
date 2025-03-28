<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\UserProfile;

use GGApis\Blizzard\Oauth\OauthAccessToken;
use GGApis\Blizzard\RegionAndLocale;

interface UserProfileApi {

    public function fetchUserProfile(OauthAccessToken $token, RegionAndLocale $regionAndLocale = null) : UserProfile;

}
