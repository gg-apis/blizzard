<?php declare(strict_types=1);

namespace GGApis\Blizzard;

enum Region : string {
    case NorthAmerica  = 'US';
    case Europe = 'EU';
    case Korea = 'KR';
    case Taiwan = 'TW';

    public function getApiNamespace() : string {
        return strtolower($this->value);
    }
}
