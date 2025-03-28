<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\CharacterProfile;

enum CharacterStatus {
    case Valid;
    case Invalid;
    case NotFound;
    case IdMismatch;
}
