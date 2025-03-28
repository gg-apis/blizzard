<?php declare(strict_types=1);

namespace GGApis\Blizzard\Test\Unit\WorldOfWarcraft\UserProfile;

use GGApis\Blizzard\BlizzardError;
use GGApis\Blizzard\Exception\Exception;
use GGApis\Blizzard\Exception\InvalidContentType;
use GGApis\Blizzard\Exception\RateThrottled;
use GGApis\Blizzard\Exception\UnableToFetchWorldOfWarcraftUserProfile;
use GGApis\Blizzard\Http\BearerTokenHeader;
use GGApis\Blizzard\Oauth\OauthAccessToken;
use GGApis\Blizzard\Oauth\Scope;
use GGApis\Blizzard\Region;
use GGApis\Blizzard\RegionAndLocale;
use GGApis\Blizzard\Test\Unit\WorldOfWarcraft\BlizzardProfileApiTestCase;
use GGApis\Blizzard\WorldOfWarcraft\BlizzardNamespace;
use GGApis\Blizzard\WorldOfWarcraft\Internal\AbstractBlizzardApi;
use GGApis\Blizzard\WorldOfWarcraft\PlayableRace;
use GGApis\Blizzard\WorldOfWarcraft\UserProfile\Account;
use GGApis\Blizzard\WorldOfWarcraft\UserProfile\AmpUserProfileApi;
use GGApis\Blizzard\WorldOfWarcraft\UserProfile\Character;
use GGApis\Blizzard\WorldOfWarcraft\UserProfile\Faction;
use GGApis\Blizzard\WorldOfWarcraft\UserProfile\Gender;
use GGApis\Blizzard\WorldOfWarcraft\UserProfile\PlayableClass;
use GGApis\Blizzard\WorldOfWarcraft\UserProfile\Realm;
use GGApis\Blizzard\WorldOfWarcraft\UserProfile\UserProfile;
use PHPUnit\Framework\Attributes\CoversClass;

#[
    CoversClass(AmpUserProfileApi::class),
    CoversClass(BearerTokenHeader::class),
    CoversClass(OauthAccessToken::class),
    CoversClass(Region::class),
    CoversClass(Account::class),
    CoversClass(Character::class),
    CoversClass(Faction::class),
    CoversClass(Gender::class),
    CoversClass(PlayableClass::class),
    CoversClass(PlayableRace::class),
    CoversClass(Realm::class),
    CoversClass(UserProfile::class),
    CoversClass(AbstractBlizzardApi::class),
    CoversClass(Exception::class),
    CoversClass(InvalidContentType::class),
    CoversClass(BlizzardError::class),
    CoversClass(RateThrottled::class),
    CoversClass(UnableToFetchWorldOfWarcraftUserProfile::class),
    CoversClass(RegionAndLocale::class),
    CoversClass(BlizzardNamespace::class),
]
class AmpUserProfileApiTest extends BlizzardProfileApiTestCase {

    private AmpUserProfileApi $subject;

    protected function setUp() : void {
        parent::setUp();
        $this->subject = new AmpUserProfileApi(
            $this->client,
            $this->config,
            $this->cache,
        );
    }

    protected function apiNamespace(Region $region) : string {
        return sprintf('profile-%s', $region->getApiNamespace());
    }

    protected function apiPath() : string {
        return '/profile/user/wow';
    }

    protected function validResponseFixtureName() : string {
        return 'fetch_wow_profile';
    }

    protected function executeApiCall(?RegionAndLocale $regionAndLocale) : object {
        return $this->subject->fetchUserProfile(
            new OauthAccessToken('access-token', 'bearer', 6000, [Scope::OpenId, Scope::WowProfile]),
            $regionAndLocale
        );
    }

    protected function assertResourceIsValid(object $resource) : void {
        self::assertInstanceOf(UserProfile::class, $resource);
        self::assertSame(2056856, $resource->id);
        self::assertCount(1, $resource->accounts);
        self::assertSame(2937834, $resource->accounts[0]->id);
        self::assertCount(3, $resource->accounts[0]->characters);
    }

    protected function expectedUnableToFetchException() : string {
        return UnableToFetchWorldOfWarcraftUserProfile::class;
    }

    protected function expectedUnableToFetchExceptionMessage() : string {
        return 'Blizzard responded with an invalid status code (403) while fetching World of Warcraft user profile.';
    }
}