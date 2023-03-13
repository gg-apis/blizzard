<?php declare(strict_types=1);

namespace GGApis\Blizzard\Oauth;

enum Scope : string {
    case WowProfile = 'wow.profile';
    case Starcraft2Profile = 'sc2.profile';
    case Diablo3Profile = 'd3.profile';
    case OpenId = 'openid';
}