<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\Internal;

use CuyZ\Valinor\Mapper\Source\Source;

interface ValinorSourceProvider {

    public function source(string $body) : Source;

}