<?php declare(strict_types=1);

namespace GGApis\Blizzard\Test\Unit\WorldOfWarcraft\ProfileApi;

use GGApis\Blizzard\BlizzardError;
use GGApis\Blizzard\Exception\Exception;
use GGApis\Blizzard\Exception\InvalidContentType;
use GGApis\Blizzard\Exception\RateThrottled;
use GGApis\Blizzard\Exception\UnableToFetchMythicKeystoneCharacterProfile;
use GGApis\Blizzard\Http\BearerTokenHeader;
use GGApis\Blizzard\Oauth\ClientAccessToken;
use GGApis\Blizzard\Region;
use GGApis\Blizzard\RegionAndLocale;
use GGApis\Blizzard\Test\Helper\FixtureUtils;
use GGApis\Blizzard\Test\Unit\WorldOfWarcraft\BlizzardProfileApiTestCase;
use GGApis\Blizzard\WorldOfWarcraft\BlizzardNamespace;
use GGApis\Blizzard\WorldOfWarcraft\Internal\AbstractBlizzardApi;
use GGApis\Blizzard\WorldOfWarcraft\MythicKeystone\AmpMythicKeystoneCharacterApi;
use GGApis\Blizzard\WorldOfWarcraft\MythicKeystone\MythicKeystoneCharacterApi;
use GGApis\Blizzard\WorldOfWarcraft\MythicKeystone\MythicKeystoneCharacterProfile;
use GGApis\Blizzard\WorldOfWarcraft\MythicKeystone\MythicKeystoneRating;
use GGApis\Blizzard\WorldOfWarcraft\PlayableRace;
use GGApis\Blizzard\WorldOfWarcraft\RgbaColor;
use GGApis\Blizzard\WorldOfWarcraft\UserProfile\Character;
use GGApis\Blizzard\WorldOfWarcraft\UserProfile\Faction;
use GGApis\Blizzard\WorldOfWarcraft\UserProfile\Gender;
use GGApis\Blizzard\WorldOfWarcraft\UserProfile\PlayableClass;
use GGApis\Blizzard\WorldOfWarcraft\UserProfile\Realm;
use PHPUnit\Framework\Attributes\CoversClass;

#[
    CoversClass(MythicKeystoneCharacterApi::class),
    CoversClass(BearerTokenHeader::class),
    CoversClass(ClientAccessToken::class),
    CoversClass(Region::class),
    CoversClass(Character::class),
    CoversClass(Faction::class),
    CoversClass(Gender::class),
    CoversClass(AbstractBlizzardApi::class),
    CoversClass(MythicKeystoneCharacterProfile::class),
    CoversClass(MythicKeystoneRating::class),
    CoversClass(PlayableClass::class),
    CoversClass(PlayableRace::class),
    CoversClass(AmpMythicKeystoneCharacterApi::class),
    CoversClass(Realm::class),
    CoversClass(RgbaColor::class),
    CoversClass(Exception::class),
    CoversClass(InvalidContentType::class),
    CoversClass(BlizzardError::class),
    CoversClass(RateThrottled::class),
    CoversClass(UnableToFetchMythicKeystoneCharacterProfile::class),
    CoversClass(RegionAndLocale::class),
    CoversClass(BlizzardNamespace::class),
]
class AmpMythicKeystoneCharacterApiFetchCharacterProfileNoRatingTest extends BlizzardProfileApiTestCase {

    private MythicKeystoneCharacterApi $subject;
    private Character $character;

    protected function setUp() : void {
        parent::setUp();
        $this->subject = new AmpMythicKeystoneCharacterApi(
            $this->client,
            $this->config,
            $this->cache,
            $this->mapper
        );
        $this->character = FixtureUtils::adaxion();
    }

    protected function apiNamespace(Region $region) : string {
        return sprintf('profile-%s', $region->getApiNamespace());
    }

    protected function apiPath() : string {
        return '/profile/wow/character/area-52/adaxion/mythic-keystone-profile';
    }

    protected function expectedUnableToFetchException() : string {
        return UnableToFetchMythicKeystoneCharacterProfile::class;
    }

    protected function expectedUnableToFetchExceptionMessage() : string {
        return 'Blizzard responded with an invalid status code (403) while fetching Mythic Keystone character profile.';
    }

    protected function validResponseFixtureName() : string {
        return 'fetch_mythic_keystone_character_profile_no_rating';
    }

    protected function executeApiCall(?RegionAndLocale $regionAndLocale) : object {
        return $this->subject->fetchMythicKeystoneCharacterProfile(
            new ClientAccessToken('access-token', 'bearer', 5000, 'sub-string'),
            $this->character,
            $regionAndLocale
        );
    }

    protected function assertResourceIsValid(object $resource) : void {
        self::assertInstanceOf(MythicKeystoneCharacterProfile::class, $resource);
        self::assertSame($this->character, $resource->character);
        self::assertNull($resource->rating);
        self::assertSame(876, $resource->currentPeriodId);
        self::assertSame([], $resource->seasonIds);
    }
}
