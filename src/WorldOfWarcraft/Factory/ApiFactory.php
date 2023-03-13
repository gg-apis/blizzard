<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\Factory;

use Amp\Cache\Cache;
use Amp\Http\Client\HttpClient;
use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\Interceptor\SetRequestHeader;
use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\MapperBuilder;
use GGApis\Blizzard\ApiConfig;
use GGApis\Blizzard\Oauth\AmpAuthenticationApi;
use GGApis\Blizzard\Oauth\AuthenticationApi;
use GGApis\Blizzard\Oauth\AuthenticationStateValidator;
use GGApis\Blizzard\WorldOfWarcraft\ProfileApi\AmpCharacterProfileApi;
use GGApis\Blizzard\WorldOfWarcraft\ProfileApi\AmpMythicKeystoneCharacterApi;
use GGApis\Blizzard\WorldOfWarcraft\ProfileApi\AmpUserProfileApi;
use GGApis\Blizzard\WorldOfWarcraft\ProfileApi\CharacterProfileApi;
use GGApis\Blizzard\WorldOfWarcraft\ProfileApi\MythicKeystoneCharacterApi;
use GGApis\Blizzard\WorldOfWarcraft\ProfileApi\UserProfileApi;

final class ApiFactory {

    private readonly HttpClient $client;
    private readonly TreeMapper $mapper;

    public function __construct(
        private readonly ApiConfig $config,
        private readonly Cache $cache,
    ) {
        $this->client = (new HttpClientBuilder())->intercept(
            new SetRequestHeader('User-Agent', 'gg-apis/blizzard v0.1.0')
        )->build();
        $this->mapper = (new MapperBuilder())->allowSuperfluousKeys()->mapper();
    }

    #[ServiceDelegate]
    public function authenticationApi(AuthenticationStateValidator $stateValidator) : AuthenticationApi {
        return new AmpAuthenticationApi(
            $this->client,
            $this->config,
            $this->cache,
            $this->mapper,
            $stateValidator
        );
    }

    #[ServiceDelegate]
    public function characterProfileApi() : CharacterProfileApi {
        return new AmpCharacterProfileApi(
            $this->client,
            $this->config,
            $this->cache,
            $this->mapper
        );
    }

    #[ServiceDelegate]
    public function mythicKeystoneCharacterApi() : MythicKeystoneCharacterApi {
        return new AmpMythicKeystoneCharacterApi(
            $this->client,
            $this->config,
            $this->cache,
            $this->mapper
        );
    }

    #[ServiceDelegate]
    public function userProfileApi() : UserProfileApi {
        return new AmpUserProfileApi(
            $this->client,
            $this->config,
            $this->cache,
            $this->mapper
        );
    }

}