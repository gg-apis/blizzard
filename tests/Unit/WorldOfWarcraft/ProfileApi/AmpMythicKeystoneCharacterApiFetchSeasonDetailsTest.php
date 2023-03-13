<?php declare(strict_types=1);

namespace GGApis\Blizzard\Test\Unit\WorldOfWarcraft\ProfileApi;

use GGApis\Blizzard\BlizzardError;
use GGApis\Blizzard\Exception\Exception;
use GGApis\Blizzard\Exception\InvalidContentType;
use GGApis\Blizzard\Exception\RateThrottled;
use GGApis\Blizzard\Exception\UnableToFetchMythicKeystoneCharacterSeasonDetails;
use GGApis\Blizzard\Http\BearerTokenHeader;
use GGApis\Blizzard\Oauth\ClientAccessToken;
use GGApis\Blizzard\Region;
use GGApis\Blizzard\RegionAndLocale;
use GGApis\Blizzard\Test\Helper\FixtureUtils;
use GGApis\Blizzard\Test\Unit\WorldOfWarcraft\BlizzardProfileApiTestCase;
use GGApis\Blizzard\WorldOfWarcraft\BlizzardNamespace;
use GGApis\Blizzard\WorldOfWarcraft\Character;
use GGApis\Blizzard\WorldOfWarcraft\Faction;
use GGApis\Blizzard\WorldOfWarcraft\Gender;
use GGApis\Blizzard\WorldOfWarcraft\Internal\AbstractBlizzardApi;
use GGApis\Blizzard\WorldOfWarcraft\MythicKeystoneAffix;
use GGApis\Blizzard\WorldOfWarcraft\MythicKeystoneCharacterSeasonDetails;
use GGApis\Blizzard\WorldOfWarcraft\MythicKeystoneDungeon;
use GGApis\Blizzard\WorldOfWarcraft\MythicKeystonePartyMember;
use GGApis\Blizzard\WorldOfWarcraft\MythicKeystoneRating;
use GGApis\Blizzard\WorldOfWarcraft\MythicKeystoneRun;
use GGApis\Blizzard\WorldOfWarcraft\PlayableClass;
use GGApis\Blizzard\WorldOfWarcraft\PlayableRace;
use GGApis\Blizzard\WorldOfWarcraft\PlayableSpecialization;
use GGApis\Blizzard\WorldOfWarcraft\ProfileApi\AmpMythicKeystoneCharacterApi;
use GGApis\Blizzard\WorldOfWarcraft\Realm;
use GGApis\Blizzard\WorldOfWarcraft\RgbaColor;
use PHPUnit\Framework\Attributes\CoversClass;

#[
    CoversClass(AmpMythicKeystoneCharacterApi::class),
    CoversClass(BearerTokenHeader::class),
    CoversClass(ClientAccessToken::class),
    CoversClass(Region::class),
    CoversClass(RegionAndLocale::class),
    CoversClass(Character::class),
    CoversClass(Faction::class),
    CoversClass(Gender::class),
    CoversClass(AbstractBlizzardApi::class),
    CoversClass(MythicKeystoneAffix::class),
    CoversClass(MythicKeystoneCharacterSeasonDetails::class),
    CoversClass(MythicKeystoneDungeon::class),
    CoversClass(MythicKeystoneRating::class),
    CoversClass(MythicKeystoneRun::class),
    CoversClass(PlayableClass::class),
    CoversClass(PlayableRace::class),
    CoversClass(Realm::class),
    CoversClass(RgbaColor::class),
    CoversClass(Exception::class),
    CoversClass(InvalidContentType::class),
    CoversClass(BlizzardError::class),
    CoversClass(UnableToFetchMythicKeystoneCharacterSeasonDetails::class),
    CoversClass(RateThrottled::class),
    CoversClass(PlayableSpecialization::class),
    CoversClass(MythicKeystonePartyMember::class),
    CoversClass(BlizzardNamespace::class),
]
class AmpMythicKeystoneCharacterApiFetchSeasonDetailsTest extends BlizzardProfileApiTestCase {

    private AmpMythicKeystoneCharacterApi $subject;
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

    protected function getApiNamespace(Region $region) : string {
        return sprintf('profile-%s', $region->getApiNamespace());
    }

    protected function getApiPath() : string {
        return '/profile/wow/character/area-52/adaxion/mythic-keystone-profile/season/9';
    }

    protected function getExpectedUnableToFetchException() : string {
        return UnableToFetchMythicKeystoneCharacterSeasonDetails::class;
    }

    protected function getExpectedUnableToFetchExceptionMessage() : string {
        return 'Blizzard responded with an invalid status code (403) while fetching Mythic Keystone character season details.';
    }

    protected function getValidResponseFixtureName() : string {
        return 'fetch_mythic_keystone_character_season_details';
    }

    protected function executeApiCall(?RegionAndLocale $regionAndLocale) : object {
        return $this->subject->fetchMythicKeystoneCharacterSeasonDetails(
            new ClientAccessToken('access-token', 'bearer', 5000, 'sub-string'),
            $this->character,
            9,
            $regionAndLocale
        );
    }

    protected function assertResourceIsValid(object $resource) : void {
        self::assertInstanceOf(MythicKeystoneCharacterSeasonDetails::class, $resource);
        self::assertSame(9, $resource->seasonId);
        self::assertSame($this->character, $resource->character);
        self::assertSame(2323.952, $resource->rating->rating);
        self::assertCount(16, $resource->bestRuns);


        $run = $resource->bestRuns[0];
        self::assertInstanceOf(MythicKeystoneRun::class, $run);
        self::assertSame(17, $run->keystoneLevel);
        self::assertSame('The Nokhud Offensive', $run->dungeon->name);
        self::assertFalse($run->isCompletedWithinTime);
        self::assertSame(2623734, $run->duration->s);
        self::assertCount(4, $run->affixes);
        self::assertCount(5, $run->members);
        self::assertSame(142.83472, $run->rating->rating);
        self::assertSame(280.79675, $run->mapRating->rating);

        $member = $run->members[0];
        self::assertInstanceOf(MythicKeystonePartyMember::class, $member);
        self::assertSame(175591090, $member->id);
        self::assertSame('Jump', $member->name);
        self::assertSame(11, $member->realmId);
        self::assertSame('Frost', $member->specialization->name);
        self::assertSame('Orc', $member->race->name);
        self::assertSame(395, $member->equippedItemLevel);
    }
}