<?php declare(strict_types=1);

namespace GGApis\Blizzard\Test\Unit\WorldOfWarcraft\ProfileApi;

use Amp\Cache\Cache;
use Amp\Cache\LocalCache;
use Amp\Http\Client\HttpClient;
use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\Request;
use Amp\Http\HttpStatus;
use Cspray\HttpClientTestInterceptor\HttpMockAwareTestTrait;
use Cspray\HttpRequestBuilder\RequestBuilder;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\MapperBuilder;
use GGApis\Blizzard\ApiConfig;
use GGApis\Blizzard\BlizzardError;
use GGApis\Blizzard\Exception\Exception;
use GGApis\Blizzard\Exception\InvalidContentType;
use GGApis\Blizzard\Exception\RateThrottled;
use GGApis\Blizzard\Exception\UnableToFetchCharacterStatus;
use GGApis\Blizzard\Http\BearerTokenHeader;
use GGApis\Blizzard\Oauth\ClientAccessToken;
use GGApis\Blizzard\Region;
use GGApis\Blizzard\Test\Helper\FixtureUtils;
use GGApis\Blizzard\Test\Helper\MockApiConfig;
use GGApis\Blizzard\Test\Helper\MockBlizzardResponseBuilder;
use GGApis\Blizzard\Test\Helper\UriUtils;
use GGApis\Blizzard\WorldOfWarcraft\BlizzardNamespace;
use GGApis\Blizzard\WorldOfWarcraft\Character;
use GGApis\Blizzard\WorldOfWarcraft\CharacterStatus;
use GGApis\Blizzard\WorldOfWarcraft\Faction;
use GGApis\Blizzard\WorldOfWarcraft\Gender;
use GGApis\Blizzard\WorldOfWarcraft\Internal\AbstractBlizzardApi;
use GGApis\Blizzard\WorldOfWarcraft\PlayableClass;
use GGApis\Blizzard\WorldOfWarcraft\PlayableRace;
use GGApis\Blizzard\WorldOfWarcraft\ProfileApi\AmpCharacterProfileApi;
use GGApis\Blizzard\WorldOfWarcraft\Realm;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

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
class AmpCharacterProfileApiCharacterStatusTest extends TestCase {

    use HttpMockAwareTestTrait;

    private HttpClient $client;
    private ApiConfig $config;
    private Cache $cache;
    private TreeMapper $mapper;
    private AmpCharacterProfileApi $subject;

    protected function setUp() : void {
        $this->client = (new HttpClientBuilder())->intercept($this->getMockingInterceptor())->build();
        $this->config = new MockApiConfig();
        $this->cache = new LocalCache();
        $this->mapper = (new MapperBuilder())->allowSuperfluousKeys()->mapper();
        $this->subject = new AmpCharacterProfileApi(
            $this->client,
            $this->config,
            $this->cache,
            $this->mapper
        );
    }

    protected function assertPostConditions() : void {
        $this->validateHttpMocks();
    }

    private function request(Region $region = null) : Request {
        $region ??= $this->config->getDefaultRegion();
        return RequestBuilder::withHeaders([
            'Authorization' => 'Bearer access-token',
            'Battlenet-Namespace' => BlizzardNamespace::Profile->toString($region),
        ])->get(
            UriUtils::apiUriForRegion($region)
                ->withPath('/profile/wow/character/area-52/adaxion/status')
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
            new ClientAccessToken('access-token', 'bearer', 5000, 'sub-string'),
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
            new ClientAccessToken('access-token', 'bearer', 5000, 'sub-string'),
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
            new ClientAccessToken('access-token', 'bearer', 5000, 'sub-string'),
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
            new ClientAccessToken('access-token', 'bearer', 5000, 'sub-string'),
            FixtureUtils::adaxion()
        );

        self::assertSame(CharacterStatus::IdMismatch, $status);
    }

    public function testCharacterStatusDoesNotReturnJsonThrowsException() : void {
        $this->httpMock()->onRequest($request = $this->request())->returnResponse(
            MockBlizzardResponseBuilder::fromNotJsonResponse($request)
        );

        $this->expectException(InvalidContentType::class);
        $this->expectExceptionMessage('Expected Content-Type of "application/json" but received "text/plain".');

        $this->subject->fetchCharacterStatus(
            new ClientAccessToken('access-token', 'bearer', 5000, 'sub-string'),
            FixtureUtils::adaxion()
        );
    }

    public function testCharacterStatusWithRateThrottledRequestThrowsException() : void {
        $this->httpMock()
            ->onRequest(
                $request = $this->request()
            )->returnResponse(
                MockBlizzardResponseBuilder::fromJsonResponse($request, HttpStatus::TOO_MANY_REQUESTS, [
                    'code' => HttpStatus::TOO_MANY_REQUESTS,
                    'type' => 'BLZWEBAPI00000429',
                    'detail' => 'Too Many Requests'
                ])
            );

        $this->expectException(RateThrottled::class);
        $this->expectExceptionMessage(
            'Blizzard has throttled requests. Please wait before additional requests are made.'
        );

        $this->subject->fetchCharacterStatus(
            new ClientAccessToken('access-token', 'bearer', 5000, 'sub-string'),
            FixtureUtils::adaxion()
        );
    }

    public function testCharacterStatusWithInvalidStatusCodeThrowsException() : void {
        $this->httpMock()
            ->onRequest(
                $request = $this->request()
            )->returnResponse(
                MockBlizzardResponseBuilder::fromJsonResponse($request, HttpStatus::FORBIDDEN, [
                    'code' => HttpStatus::FORBIDDEN,
                    'type' => 'BLZWEBAPI00000403',
                    'detail' => 'Forbidden'
                ])
            );

        $this->expectException(UnableToFetchCharacterStatus::class);
        $this->expectExceptionMessage(
            'Blizzard responded with an invalid status code (403) while fetching Character status.',
        );

        $this->subject->fetchCharacterStatus(
            new ClientAccessToken('access-token', 'bearer', 5000, 'sub-string'),
            FixtureUtils::adaxion()
        );
    }

}