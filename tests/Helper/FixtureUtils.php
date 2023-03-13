<?php declare(strict_types=1);

namespace GGApis\Blizzard\Test\Helper;

use GGApis\Blizzard\WorldOfWarcraft\Character;
use GGApis\Blizzard\WorldOfWarcraft\Faction;
use GGApis\Blizzard\WorldOfWarcraft\Gender;
use GGApis\Blizzard\WorldOfWarcraft\PlayableClass;
use GGApis\Blizzard\WorldOfWarcraft\PlayableRace;
use GGApis\Blizzard\WorldOfWarcraft\Realm;

final class FixtureUtils {

    private function __construct() {
    }

    public static function getMockBlizzardResponse(string $fixtureName) : string {
        return file_get_contents(
            sprintf('%s/%s.json', self::fixtureDir(), $fixtureName)
        );
    }

    public static function adaxion() : Character {
        return new Character(
            1234,
            'Adaxion',
            new Realm(9876, 'Area 52', 'area-52'),
            new PlayableRace(1, 'Dracthyr'),
            new PlayableClass(1, 'Evoker'),
            70,
            new Gender('FEMALE', 'Female'),
            new Faction('HORDE', 'Horde')
        );
    }

    private static function fixtureDir() : string {
        return dirname(__DIR__) . '/Fixture/mock_blizzard';
    }

}