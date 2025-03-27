<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\UserProfile;

use Cspray\AnnotatedContainer\Attribute\Service;
use GGApis\Blizzard\Oauth\OauthAccessToken;
use GGApis\Blizzard\RegionAndLocale;

#[Service]
interface UserProfileApi {

    public function fetchUserProfile(OauthAccessToken $token, RegionAndLocale $regionAndLocale = null) : UserProfile;

}
