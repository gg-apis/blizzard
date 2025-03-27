<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\PetCollection;

enum PetQuality : string {
    case Poor = "POOR";
    case Common = "COMMON";
    case Uncommon = "UNCOMMON";
    case Rare = "RARE";
}