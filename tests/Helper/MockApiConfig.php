<?php declare(strict_types=1);

namespace GGApis\Blizzard\Test\Helper;

use GGApis\Blizzard\ApiConfig;
use GGApis\Blizzard\Locale;
use GGApis\Blizzard\Region;
use League\Uri\Http;
use Psr\Http\Message\UriInterface;

final class MockApiConfig implements ApiConfig {
    public function getClientId() : string {
        return 'known-client-id';
    }

    public function getClientSecret() : string {
        return 'known-client-secret';
    }

    public function getAuthTokenRedirectUri() : UriInterface {
        return Http::createFromString('http://localhost/example/redirect');
    }

    public function getDefaultRegion() : Region {
        return Region::NorthAmerica;
    }

    public function getDefaultLocale() : Locale {
        return Locale::EnglishUnitedStates;
    }
}
