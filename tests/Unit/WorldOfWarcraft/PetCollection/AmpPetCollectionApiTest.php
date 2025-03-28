<?php declare(strict_types=1);

namespace GGApis\Blizzard\Test\Unit\WorldOfWarcraft\PetCollection;

use GGApis\Blizzard\Exception\UnableToFetchPetCollection;
use GGApis\Blizzard\Oauth\OauthAccessToken;
use GGApis\Blizzard\Oauth\Scope;
use GGApis\Blizzard\Region;
use GGApis\Blizzard\RegionAndLocale;
use GGApis\Blizzard\Test\Unit\WorldOfWarcraft\BlizzardProfileApiTestCase;
use GGApis\Blizzard\WorldOfWarcraft\BlizzardNamespace;
use GGApis\Blizzard\WorldOfWarcraft\PetCollection\AmpPetCollectionApi;
use GGApis\Blizzard\WorldOfWarcraft\PetCollection\PetCollectionSummary;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AmpPetCollectionApi::class)]
class AmpPetCollectionApiTest extends BlizzardProfileApiTestCase {

    private AmpPetCollectionApi $subject;

    protected function setUp() : void {
        parent::setUp();
        $this->subject = new AmpPetCollectionApi(
            $this->client,
            $this->config,
            $this->cache,
        );
    }

    protected function apiNamespace(Region $region) : string {
        return BlizzardNamespace::Profile->toString($region);
    }

    protected function apiPath() : string {
        return '/profile/user/wow/collections/pets';
    }

    protected function expectedUnableToFetchException() : string {
        return UnableToFetchPetCollection::class;
    }

    protected function expectedUnableToFetchExceptionMessage() : string {
        return 'Blizzard responded with an invalid status code (403) while fetching pet collection summary.';
    }

    protected function validResponseFixtureName() : string {
        return 'fetch_pet_collection';
    }

    protected function executeApiCall(?RegionAndLocale $regionAndLocale) : object {
        return $this->subject->fetchPetCollectionSummary(
            new OauthAccessToken('access-token', 'bearer', 400, [Scope::OpenId, Scope::WowProfile]),
            $regionAndLocale
        );
    }

    protected function assertResourceIsValid(object $resource) : void {
        self::assertInstanceOf(PetCollectionSummary::class, $resource);
        self::assertCount(5, $resource);
    }
}