<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\UserProfile;

use GGApis\Blizzard\Exception\UnableToFetchWorldOfWarcraftUserProfile;
use GGApis\Blizzard\Oauth\OauthAccessToken;
use GGApis\Blizzard\RegionAndLocale;
use GGApis\Blizzard\WorldOfWarcraft\BlizzardNamespace;
use GGApis\Blizzard\WorldOfWarcraft\Internal\AbstractBlizzardApi;
use GGApis\Blizzard\WorldOfWarcraft\Internal\BlizzardErrorMappingExceptionThrowingFetchErrorHandler;
use GGApis\Blizzard\WorldOfWarcraft\Internal\ValinorJsonMappingSourceProvider;
use GGApis\Blizzard\WorldOfWarcraft\Internal\ValinorMappingHydrator;
use GGApis\Blizzard\WorldOfWarcraft\Internal\ValinorSourceProvider;

final class AmpUserProfileApi extends AbstractBlizzardApi implements UserProfileApi {

    public function fetchUserProfile(OauthAccessToken $token, RegionAndLocale $regionAndLocale = null) : UserProfile {
        $resource = $this->processFetchResourceRequest(
            $token,
            '/profile/user/wow',
            BlizzardNamespace::Profile,
            new ValinorMappingHydrator(
                $this->simpleMapper(),
                $this->sourceProvider(),
                UserProfile::class,
            ),
            new BlizzardErrorMappingExceptionThrowingFetchErrorHandler(
                UnableToFetchWorldOfWarcraftUserProfile::fromBlizzardError(...)
            ),
            $regionAndLocale
        );

        assert($resource instanceof UserProfile);

        return $resource;
    }

    private function sourceProvider() : ValinorSourceProvider {
        return new ValinorJsonMappingSourceProvider([
            'wow_accounts' => 'accounts',
            'wow_accounts.*.characters.*.playable_race' => 'race',
            'wow_accounts.*.characters.*.playable_class' => 'class'
        ]);
    }

}
