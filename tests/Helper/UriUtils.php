<?php declare(strict_types=1);

namespace GGApis\Blizzard\Test\Helper;

use GGApis\Blizzard\Locale;
use GGApis\Blizzard\Region;
use League\Uri\Components\Query;
use League\Uri\Http;
use Psr\Http\Message\UriInterface;

class UriUtils {

    private function __construct() {}

    public static function apiUriForRegion(Region $region) : UriInterface {
        return Http::createFromString(sprintf(
            'https://%s.api.blizzard.com',
            $region->getApiNamespace(),
        ));
    }

    public static function uriWithLocale(UriInterface $uri, Locale $locale) : UriInterface {
        return $uri->withQuery(
            Query::createFromParams(['locale' => $locale->value])
        );
    }

}
