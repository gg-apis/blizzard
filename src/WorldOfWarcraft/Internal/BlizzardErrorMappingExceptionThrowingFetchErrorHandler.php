<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\Internal;

use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\Mapper\TreeMapper;
use GGApis\Blizzard\BlizzardError;

final class BlizzardErrorMappingExceptionThrowingFetchErrorHandler implements FetchErrorHandler {

    public function __construct(
        private readonly \Closure $exceptionFactory,
    ) {}

    public function handle(int $status, string $responseBody) : ?object {
        $json = json_decode($responseBody);
        $blizzardError = new BlizzardError($json->code, $json->type, $json->detail);
        throw ($this->exceptionFactory)($blizzardError);
    }
}