<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\Internal;

use Amp\Cache\Cache;
use Amp\Http\Client\HttpClient;
use Amp\Http\Client\Response;
use Amp\Http\Status;
use Closure;
use Cspray\HttpRequestBuilder\RequestBuilder;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\Mapper\TreeMapper;
use GGApis\Blizzard\ApiConfig;
use GGApis\Blizzard\BlizzardError;
use GGApis\Blizzard\Exception\InvalidContentType;
use GGApis\Blizzard\Exception\RateThrottled;
use GGApis\Blizzard\Http\BearerTokenHeader;
use GGApis\Blizzard\Locale;
use GGApis\Blizzard\Oauth\ClientAccessToken;
use GGApis\Blizzard\Oauth\OauthAccessToken;
use GGApis\Blizzard\Region;
use GGApis\Blizzard\RegionAndLocale;
use GGApis\Blizzard\Test\Helper\UriUtils;
use GGApis\Blizzard\WorldOfWarcraft\BlizzardNamespace;
use League\Uri\Components\Query;
use League\Uri\Http;
use Psr\Http\Message\UriInterface;

abstract class AbstractBlizzardApi {

    public function __construct(
        protected readonly HttpClient $client,
        protected readonly ApiConfig $config,
        protected readonly Cache $cache,
        protected readonly TreeMapper $mapper
    ) {}

    protected function processFetchResourceRequest(
        ClientAccessToken|OauthAccessToken $token,
        string $path,
        BlizzardNamespace $namespace,
        Closure $hydrator,
        Closure $unableToFetchExceptionProvider,
        RegionAndLocale $regionAndLocale = null
    ) : object {
        $region = $this->getRegion($regionAndLocale);
        $locale = $this->getLocale($regionAndLocale);

        $uri = $this->apiUriForRegion($region)
            ->withPath($path)
            ->withQuery(
                Query::createFromParams(['locale' => $locale->value])
            );

        $headers = [
            'Authorization' => BearerTokenHeader::fromToken($token->accessToken)->toString(),
            'Battlenet-Namespace' => $namespace->toString($region)
        ];
        $cacheKey = md5((string) $uri);
        $entry = $this->cache->get($cacheKey);
        if ($entry !== null) {
            $headers['If-Modified-Since'] = $entry['lastModified'];
        }

        $response = $this->client->request(RequestBuilder::withHeaders($headers)->get($uri));

        if ($response->getStatus() === Status::NOT_MODIFIED) {
            return $hydrator($entry['content']);
        }

        $this->validateContentTypeIsJson($response);

        $status = $response->getStatus();
        $body = $response->getBody()->buffer();

        $this->validateRateThrottlingNotActive($status, $body);
        $this->validateIsSuccessfulResponse($status, $body, $unableToFetchExceptionProvider);

        $this->cache->set($cacheKey, [
            'lastModified' => $response->getHeader('Last-Modified'),
            'content' => $body
        ]);

        return $hydrator($body);
    }

    final protected function validateContentTypeIsJson(Response $response) : void {
        $contentType = $response->getHeader('Content-Type');
        if (!str_contains($contentType, 'application/json')) {
            throw InvalidContentType::fromInvalidContentType($contentType);
        }
    }

    final protected function validateRateThrottlingNotActive(int $status, string $body) : void {
        if ($status === Status::TOO_MANY_REQUESTS) {
            throw RateThrottled::fromRequestThrottled(
                $this->mapper->map(BlizzardError::class, Source::json($body))
            );
        }
    }

    final protected function validateIsSuccessfulResponse(int $status, string $body, Closure $exceptionFactory) : void {
        if ($status !== Status::OK) {
            $blizzardError = $this->mapper->map(BlizzardError::class, Source::json($body));
            throw $exceptionFactory($blizzardError);
        }
    }

    final protected function getRegion(RegionAndLocale $regionAndLocale = null) : Region {
        return $regionAndLocale?->region ?? $this->config->getDefaultRegion();
    }

    final protected function getLocale(RegionAndLocale $regionAndLocale = null) : Locale {
        return $regionAndLocale?->locale ?? $this->config->getDefaultLocale();
    }

    final protected function apiUriForRegion(Region $region) : UriInterface {
        return Http::createFromString(sprintf(
            'https://%s.api.blizzard.com',
            $region->getApiNamespace()
        ));
    }

}
