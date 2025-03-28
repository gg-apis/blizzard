<?php declare(strict_types=1);

namespace GGApis\Blizzard\Test\Unit\WorldOfWarcraft;

use Amp\Cache\Cache;
use Amp\Cache\LocalCache;
use Amp\Http\Client\HttpClient;
use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\HttpStatus;
use Cspray\HttpClientTestInterceptor\HttpMockAwareTestTrait;
use Cspray\HttpRequestBuilder\RequestBuilder;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\MapperBuilder;
use DateTimeImmutable;
use DateTimeInterface;
use GGApis\Blizzard\ApiConfig;
use GGApis\Blizzard\Exception\InvalidContentType;
use GGApis\Blizzard\Exception\RateThrottled;
use GGApis\Blizzard\Locale;
use GGApis\Blizzard\Oauth\ClientAccessToken;
use GGApis\Blizzard\Region;
use GGApis\Blizzard\RegionAndLocale;
use GGApis\Blizzard\Test\Helper\FixtureUtils;
use GGApis\Blizzard\Test\Helper\MockApiConfig;
use GGApis\Blizzard\Test\Helper\MockBlizzardResponseBuilder;
use GGApis\Blizzard\Test\Helper\UriUtils;
use PHPUnit\Framework\TestCase;
use Throwable;

abstract class BlizzardProfileApiTestCase extends TestCase {

    use HttpMockAwareTestTrait;

    protected HttpClient $client;
    protected ApiConfig $config;
    protected Cache $cache;

    protected function setUp() : void {
        $this->client = (new HttpClientBuilder())->intercept($this->getMockingInterceptor())->build();
        $this->config = new MockApiConfig();
        $this->cache = new LocalCache();
    }

    protected function assertPostConditions() : void {
        $this->validateHttpMocks();
    }

    final protected function request(
        Region $region = null,
        Locale $locale = null,
        DateTimeInterface $lastModified = null
    ) {
        $region ??= $this->config->getDefaultRegion();
        $locale ??= $this->config->getDefaultLocale();

        $builder = RequestBuilder::withHeaders([
            'Authorization' => 'Bearer access-token',
            'Battlenet-Namespace' => $this->apiNamespace($region),
        ]);

        if ($lastModified !== null) {
            $builder = $builder->addHeaders([
                'If-Modified-Since' => $lastModified->format(DateTimeInterface::RFC822)
            ]);
        }

        return $builder->get(
            UriUtils::uriWithLocale(
                UriUtils::apiUriForRegion($region),
                $locale
            )->withPath($this->apiPath())
        );
    }

    final protected function clientAccessToken() : ClientAccessToken {
        return new ClientAccessToken('access-token', 'bearer', 5000, 'sub-string');
    }

    public function testFetchingResourceWithValidRequestHydratesCorrectResource() : void {
        $this->httpMock()
            ->onRequest($request = $this->request())
            ->returnResponse(
                MockBlizzardResponseBuilder::fromJsonResponse(
                    $request,
                    HttpStatus::OK,
                    FixtureUtils::getMockBlizzardResponse($this->validResponseFixtureName()),
                    ['Last-Modified' => (new DateTimeImmutable())->format(DateTimeInterface::RFC822)]
                )
            );

        $this->assertResourceIsValid($this->executeApiCall(null));
    }

    public function testFetchingResourceReturnsInvalidContentTypeThrowsException() : void {
        $this->httpMock()
            ->onRequest(
                $request = $this->request()
            )->returnResponse(
                MockBlizzardResponseBuilder::fromNotJsonResponse($request)
            );

        $this->expectException(InvalidContentType::class);
        $this->expectExceptionMessage('Expected Content-Type of "application/json" but received "text/plain".');

        $this->executeApiCall(null);
    }

    public function testFetchingResourceRequestIsRateThrottledThrowsException() : void {
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
        $this->expectExceptionMessage('Blizzard has throttled requests. Please wait before additional requests are made.');

        $this->executeApiCall(null);
    }

    public function testFetchingResourceRespondsWithInvalidStatusCodeThrowsException() : void {
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

        $this->expectException($this->expectedUnableToFetchException());
        $this->expectExceptionMessage($this->expectedUnableToFetchExceptionMessage());

        $this->executeApiCall(null);
    }

    public function testFetchingResourceWithoutCacheEntrySetsValidResponse() : void {
        $this->httpMock()
            ->onRequest($request = $this->request())
            ->returnResponse(
                MockBlizzardResponseBuilder::fromJsonResponse(
                    $request,
                    HttpStatus::OK,
                    FixtureUtils::getMockBlizzardResponse($this->validResponseFixtureName()),
                    ['Last-Modified' => $lastModified = (new \DateTimeImmutable())->format(\DateTimeInterface::RFC822)]
                )
            );

        $cacheKey = md5($request->getUri() . 'access-token');
        self::assertNull($this->cache->get($cacheKey));

        $this->executeApiCall(null);

        $entry = $this->cache->get($cacheKey);

        self::assertNotNull($entry);
        self::assertSame([
            'lastModified' => $lastModified,
            'content' => FixtureUtils::getMockBlizzardResponse($this->validResponseFixtureName())
        ], $entry);
    }

    public function testFetchingWithUnmodifiedCachedEntryReturnsCorrectResource() : void {
        $lastModified = (new \DateTimeImmutable());
        $this->httpMock()
            ->onRequest($request = $this->request(lastModified:  $lastModified))
            ->returnResponse(
                MockBlizzardResponseBuilder::fromNotModifiedResponse($request)
            );

        $cacheKey = md5($request->getUri() . 'access-token');
        $this->cache->set($cacheKey, [
            'lastModified' => $lastModified->format(DateTimeInterface::RFC822),
            'content' => FixtureUtils::getMockBlizzardResponse($this->validResponseFixtureName())
        ]);

        $this->assertResourceIsValid($this->executeApiCall(null));
    }

    public function testFetchingResourceRespectsProvidedRegionAndLocale() : void {
        $this->httpMock()
            ->onRequest($request = $this->request(region: Region::Europe, locale: Locale::ItalianItaly))
            ->returnResponse(
                MockBlizzardResponseBuilder::fromJsonResponse(
                    $request,
                    HttpStatus::OK,
                    FixtureUtils::getMockBlizzardResponse($this->validResponseFixtureName()),
                    ['Last-Modified' => (new \DateTimeImmutable())->format(\DateTimeInterface::RFC822)]
                )
            );

        $this->assertResourceIsValid($this->executeApiCall(
            new RegionAndLocale(Region::Europe, Locale::ItalianItaly)
        ));
    }

    abstract protected function apiNamespace(Region $region) : string;

    abstract protected function apiPath() : string;

    /**
     * @return class-string<Throwable>
     */
    abstract protected function expectedUnableToFetchException() : string;

    abstract protected function expectedUnableToFetchExceptionMessage() : string;

    abstract protected function validResponseFixtureName() : string;

    abstract protected function executeApiCall(?RegionAndLocale $regionAndLocale) : object;

    abstract protected function assertResourceIsValid(object $resource) : void;

}
