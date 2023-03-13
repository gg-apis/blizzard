<?php declare(strict_types=1);

namespace GGApis\Blizzard;

use Cspray\AnnotatedContainer\Attribute\Service;
use Psr\Http\Message\UriInterface;

#[Service]
interface ApiConfig {

    public function getClientId() : string;

    public function getClientSecret() : string;

    public function getDefaultRegion() : Region;

    public function getDefaultLocale() : Locale;

    public function getAuthTokenRedirectUri() : UriInterface;

}