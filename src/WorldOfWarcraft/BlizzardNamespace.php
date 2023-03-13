<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft;

use GGApis\Blizzard\Region;

enum BlizzardNamespace : string {
    case Profile = 'profile';
    case Dynamic = 'dynamic';
    case Static = 'static';

    public function toString(Region $region) : string {
        return sprintf('%s-%s', $this->value, $region->getApiNamespace());
    }

}