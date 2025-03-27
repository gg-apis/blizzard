<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\UserProfile;

use Cspray\AnnotatedContainer\Attribute\Service;
use CuyZ\Valinor\Mapper\Source\Source;
use GGApis\Blizzard\Exception\UnableToFetchWorldOfWarcraftUserProfile;
use GGApis\Blizzard\Oauth\OauthAccessToken;
use GGApis\Blizzard\RegionAndLocale;
use GGApis\Blizzard\WorldOfWarcraft\BlizzardNamespace;
use GGApis\Blizzard\WorldOfWarcraft\Internal\AbstractBlizzardApi;

#[Service]
final class AmpUserProfileApi extends AbstractBlizzardApi implements UserProfileApi {

    public function fetchUserProfile(OauthAccessToken $token, RegionAndLocale $regionAndLocale = null) : UserProfile {
        $resource = $this->processFetchResourceRequest(
            $token,
            '/profile/user/wow',
            BlizzardNamespace::Profile,
            $this->hydrateUserProfile(...),
            UnableToFetchWorldOfWarcraftUserProfile::fromBlizzardError(...),
            $regionAndLocale
        );

        assert($resource instanceof UserProfile);

        return $resource;
    }

    private function hydrateUserProfile(string $body) : UserProfile {
        $source = Source::json($body)
            ->map([
                'wow_accounts' => 'accounts',
                'wow_accounts.*.characters.*.playable_race' => 'race',
                'wow_accounts.*.characters.*.playable_class' => 'class'
            ])->camelCaseKeys();
        return $this->mapper->map(UserProfile::class, $source);
    }

}
