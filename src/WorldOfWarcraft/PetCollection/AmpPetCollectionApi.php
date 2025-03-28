<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\PetCollection;

use GGApis\Blizzard\Exception\UnableToFetchPetCollection;
use GGApis\Blizzard\Oauth\OauthAccessToken;
use GGApis\Blizzard\RegionAndLocale;
use GGApis\Blizzard\WorldOfWarcraft\BlizzardNamespace;
use GGApis\Blizzard\WorldOfWarcraft\Internal\AbstractBlizzardApi;
use GGApis\Blizzard\WorldOfWarcraft\Internal\BlizzardErrorMappingExceptionThrowingFetchErrorHandler;
use GGApis\Blizzard\WorldOfWarcraft\Internal\ValinorJsonMappingSourceProvider;
use GGApis\Blizzard\WorldOfWarcraft\Internal\ValinorMappingHydrator;

class AmpPetCollectionApi extends AbstractBlizzardApi implements PetCollectionApi {

    public function fetchPetCollectionSummary(OauthAccessToken $accessToken, RegionAndLocale $regionAndLocale = null) : PetCollectionSummary {
        $resource = $this->processFetchResourceRequest(
            $accessToken,
            '/profile/user/wow/collections/pets',
            BlizzardNamespace::Profile,
            new ValinorMappingHydrator(
                $this->simpleMapper(),
                new ValinorJsonMappingSourceProvider([]),
                PetCollectionSummary::class
            ),
            new BlizzardErrorMappingExceptionThrowingFetchErrorHandler(UnableToFetchPetCollection::fromBlizzardError(...)),
            $regionAndLocale
        );

        assert($resource instanceof PetCollectionSummary);
        return $resource;
    }
}