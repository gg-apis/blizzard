<?php declare(strict_types=1);

namespace GGApis\Blizzard;

interface ApiConfig {

    public function getClientId() : string;

    public function getClientSecret() : string;

    public function getDefaultRegion() : Region;

    public function getDefaultLocale() : Locale;

}
