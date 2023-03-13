<?php declare(strict_types=1);

namespace GGApis\Blizzard;

enum Locale : string {
    case EnglishUnitedStates = 'en_US';
    case SpanishMexico = 'es_MX';
    case PortugeseBrazil = 'pt_BR';

    case EnglishUnitedKingdom = 'en_GB';
    case SpanishSpain = 'es_ES';
    case FrenchFrance = 'fr_FR';
    case RussianRussia = 'ru_RU';
    case GermanGermany = 'de_DE';
    case PortugesePortugal = 'pt_PT';
    case ItalianItaly = 'it_IT';

    case KoreanSouthKorea = 'ko_KR';

    case ChineseTaiwan = 'zh_TW';
}
