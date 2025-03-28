<?php declare(strict_types=1);

namespace GGApis\Blizzard\Test\Unit\WorldOfWarcraft\CharacterProfile;

use Amp\Http\HttpStatus;
use Cspray\HttpClientTestInterceptor\HttpMockAwareTestTrait;
use GGApis\Blizzard\BlizzardError;
use GGApis\Blizzard\Exception\Exception;
use GGApis\Blizzard\Exception\InvalidContentType;
use GGApis\Blizzard\Exception\RateThrottled;
use GGApis\Blizzard\Exception\UnableToFetchCharacterStatus;
use GGApis\Blizzard\Http\BearerTokenHeader;
use GGApis\Blizzard\Oauth\ClientAccessToken;
use GGApis\Blizzard\Region;
use GGApis\Blizzard\RegionAndLocale;
use GGApis\Blizzard\Test\Helper\FixtureUtils;
use GGApis\Blizzard\Test\Helper\MockBlizzardResponseBuilder;
use GGApis\Blizzard\Test\Unit\WorldOfWarcraft\BlizzardProfileApiTestCase;
use GGApis\Blizzard\WorldOfWarcraft\BlizzardNamespace;
use GGApis\Blizzard\WorldOfWarcraft\CharacterProfile\AmpCharacterProfileApi;
use GGApis\Blizzard\WorldOfWarcraft\CharacterProfile\CharacterStatus;
use GGApis\Blizzard\WorldOfWarcraft\Internal\AbstractBlizzardApi;
use GGApis\Blizzard\WorldOfWarcraft\PlayableRace;
use GGApis\Blizzard\WorldOfWarcraft\UserProfile\Character;
use GGApis\Blizzard\WorldOfWarcraft\UserProfile\Faction;
use GGApis\Blizzard\WorldOfWarcraft\UserProfile\Gender;
use GGApis\Blizzard\WorldOfWarcraft\UserProfile\PlayableClass;
use GGApis\Blizzard\WorldOfWarcraft\UserProfile\Realm;
use PHPUnit\Framework\Attributes\CoversClass;

#[
    CoversClass(AmpCharacterProfileApi::class),
    CoversClass(BearerTokenHeader::class),
    CoversClass(ClientAccessToken::class),
    CoversClass(Region::class),
    CoversClass(BlizzardNamespace::class),
    CoversClass(Character::class),
    CoversClass(Faction::class),
    CoversClass(Gender::class),
    CoversClass(AbstractBlizzardApi::class),
    CoversClass(PlayableClass::class),
    CoversClass(PlayableRace::class),
    CoversClass(Realm::class),
    CoversClass(Exception::class),
    CoversClass(InvalidContentType::class),
    CoversClass(BlizzardError::class),
    CoversClass(RateThrottled::class),
    CoversClass(UnableToFetchCharacterStatus::class),
]
class AmpCharacterProfileApiCharacterStatusTest extends BlizzardProfileApiTestCase {

    use HttpMockAwareTestTrait;
    private AmpCharacterProfileApi $subject;

    protected function setUp() : void {
        parent::setUp();
        $this->subject = new AmpCharacterProfileApi(
            $this->client,
            $this->config,
            $this->cache,
        );
    }

    public function testCharacterStatusNotFound() : void {
        $this->httpMock()
            ->onRequest(
                $request = $this->request()
            )->returnResponse(
                MockBlizzardResponseBuilder::fromJsonResponse($request, HttpStatus::NOT_FOUND, [
                    'code' => HttpStatus::NOT_FOUND,
                    'type' => 'BLZWEBAPI00000404',
                    'detail' => 'Not Found'
                ])
            );

        $status = $this->subject->fetchCharacterStatus(
            $this->clientAccessToken(),
            FixtureUtils::adaxion()
        );

        self::assertSame(CharacterStatus::NotFound, $status);
    }

    public function testCharacterStatusIsValidTrueAndIdsMatchReturnsValid() : void {
        $this->httpMock()
            ->onRequest(
                $request = $this->request()
            )->returnResponse(
                MockBlizzardResponseBuilder::fromJsonResponse($request, HttpStatus::OK, [
                    '_links' => [
                        'self' => [
                            'href' => 'https://us.api.blizzard.com/profile/wow/character/area-52/adaxion/status?namespace=profile-us'
                        ]
                    ],
                    'id' => 1234,
                    'is_valid' => true
                ])
            );

        $status = $this->subject->fetchCharacterStatus(
            $this->clientAccessToken(),
            FixtureUtils::adaxion()
        );

        self::assertSame(CharacterStatus::Valid, $status);
    }

    public function testCharacterStatusIsValidFalseAndIdsMatchReturnsInvalid() : void {
        $this->httpMock()
            ->onRequest(
                $request = $this->request()
            )->returnResponse(
                MockBlizzardResponseBuilder::fromJsonResponse($request, HttpStatus::OK, [
                    '_links' => [
                        'self' => [
                            'href' => 'https://us.api.blizzard.com/profile/wow/character/area-52/adaxion/status?namespace=profile-us'
                        ]
                    ],
                    'id' => 1234,
                    'is_valid' => false
                ])
            );

        $status = $this->subject->fetchCharacterStatus(
            $this->clientAccessToken(),
            FixtureUtils::adaxion()
        );

        self::assertSame(CharacterStatus::Invalid, $status);
    }

    public function testCharacterStatusIsValidTrueAndIdsMismatchReturnsIdMismatch() : void {
        $this->httpMock()
            ->onRequest(
                $request = $this->request()
            )->returnResponse(
                MockBlizzardResponseBuilder::fromJsonResponse($request, HttpStatus::OK, [
                    '_links' => [
                        'self' => [
                            'href' => 'https://us.api.blizzard.com/profile/wow/character/area-52/adaxion/status?namespace=profile-us'
                        ]
                    ],
                    'id' => 98765,
                    'is_valid' => true
                ])
            );

        $status = $this->subject->fetchCharacterStatus(
            $this->clientAccessToken(),
            FixtureUtils::adaxion()
        );

        self::assertSame(CharacterStatus::IdMismatch, $status);
    }

    protected function apiNamespace(Region $region) : string {
        return sprintf('profile-%s', $region->getApiNamespace());
    }

    protected function apiPath() : string {
        return '/profile/wow/character/area-52/adaxion/status';
    }

    protected function expectedUnableToFetchException() : string {
        return UnableToFetchCharacterStatus::class;
    }

    protected function expectedUnableToFetchExceptionMessage() : string {
        return 'Blizzard responded with an invalid status code (403) while fetching Character status.';
    }

    protected function validResponseFixtureName() : string {
        return 'fetch_character_status';
    }

    protected function executeApiCall(?RegionAndLocale $regionAndLocale) : object {
        return $this->subject->fetchCharacterStatus(
            $this->clientAccessToken(),
            FixtureUtils::adaxion(),
            $regionAndLocale
        );
    }

    protected function assertResourceIsValid(object $resource) : void {
        self::assertInstanceOf(CharacterStatus::class, $resource);
    }
}