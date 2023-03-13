<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\ProfileApi;

use Cspray\AnnotatedContainer\Attribute\Service;
use GGApis\Blizzard\Oauth\OauthAccessToken;
use GGApis\Blizzard\RegionAndLocale;
use GGApis\Blizzard\WorldOfWarcraft\UserProfile;

#[Service]
interface UserProfileApi {

    public function fetchUserProfile(OauthAccessToken $token, RegionAndLocale $regionAndLocale = null) : UserProfile;

}
