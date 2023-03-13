<?php declare(strict_types=1);

namespace GGApis\Blizzard;

final class RegionAndLocale {

    public function __construct(
        public readonly Region $region,
        public readonly Locale $locale
    ) {}

}
