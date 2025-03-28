<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\Factory;

use Amp\Cache\Cache;
use Amp\Http\Client\HttpClient;
use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\Interceptor\SetRequestHeader;
use GGApis\Blizzard\ApiConfig;
use GGApis\Blizzard\Oauth\AmpAuthenticationApi;
use GGApis\Blizzard\Oauth\AuthenticationApi;
use GGApis\Blizzard\WorldOfWarcraft\CharacterProfile\AmpCharacterProfileApi;
use GGApis\Blizzard\WorldOfWarcraft\CharacterProfile\CharacterProfileApi;
use GGApis\Blizzard\WorldOfWarcraft\MythicKeystone\AmpMythicKeystoneCharacterApi;
use GGApis\Blizzard\WorldOfWarcraft\MythicKeystone\MythicKeystoneCharacterApi;
use GGApis\Blizzard\WorldOfWarcraft\PetCollection\AmpPetCollectionApi;
use GGApis\Blizzard\WorldOfWarcraft\PetCollection\PetCollectionApi;
use GGApis\Blizzard\WorldOfWarcraft\UserProfile\AmpUserProfileApi;
use GGApis\Blizzard\WorldOfWarcraft\UserProfile\UserProfileApi;

final class ApiFactory {

    private readonly HttpClient $client;

    public function __construct(
        private readonly ApiConfig $config,
        private readonly Cache $cache,
    ) {
        $this->client = (new HttpClientBuilder())->intercept(
            new SetRequestHeader('User-Agent', 'gg-apis/blizzard v0.1.0')
        )->build();
    }

    public function authenticationApi() : AuthenticationApi {
        return new AmpAuthenticationApi(
            $this->client,
            $this->config,
            $this->cache,
        );
    }

    public function characterProfileApi() : CharacterProfileApi {
        return new AmpCharacterProfileApi(
            $this->client,
            $this->config,
            $this->cache,
        );
    }

    public function mythicKeystoneCharacterApi() : MythicKeystoneCharacterApi {
        return new AmpMythicKeystoneCharacterApi(
            $this->client,
            $this->config,
            $this->cache,
        );
    }

    public function userProfileApi() : UserProfileApi {
        return new AmpUserProfileApi(
            $this->client,
            $this->config,
            $this->cache,
        );
    }

    public function petCollectionApi() : PetCollectionApi {
        return new AmpPetCollectionApi(
            $this->client,
            $this->config,
            $this->cache
        );
    }

}