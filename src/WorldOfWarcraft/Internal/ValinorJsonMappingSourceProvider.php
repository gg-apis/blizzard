<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\Internal;

use CuyZ\Valinor\Mapper\Source\Source;

class ValinorJsonMappingSourceProvider implements ValinorSourceProvider {

    public function __construct(
       private readonly array $map
    ) {}

    public function source(string $body) : Source {
        return Source::json($body)
            ->map($this->map)
            ->camelCaseKeys();
    }
}