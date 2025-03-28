<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\CharacterProfile;

use Amp\Http\HttpStatus;
use GGApis\Blizzard\Exception\UnableToFetchCharacterStatus;
use GGApis\Blizzard\Oauth\ClientAccessToken;
use GGApis\Blizzard\RegionAndLocale;
use GGApis\Blizzard\WorldOfWarcraft\BlizzardNamespace;
use GGApis\Blizzard\WorldOfWarcraft\Internal\AbstractBlizzardApi;
use GGApis\Blizzard\WorldOfWarcraft\Internal\BlizzardErrorMappingExceptionThrowingFetchErrorHandler;
use GGApis\Blizzard\WorldOfWarcraft\Internal\FetchErrorHandler;
use GGApis\Blizzard\WorldOfWarcraft\Internal\JsonDecodingHydrator;
use GGApis\Blizzard\WorldOfWarcraft\UserProfile\Character;

class AmpCharacterProfileApi extends AbstractBlizzardApi  implements CharacterProfileApi {

    public function fetchCharacterStatus(ClientAccessToken $token, Character $character, RegionAndLocale $regionAndLocale = null) : CharacterStatus {
        $path = sprintf(
            '/profile/wow/character/%s/%s/status',
            $character->realm->slug,
            strtolower($character->name)
        );

        $characterStatus = $this->processFetchResourceRequest(
            $token,
            $path,
            BlizzardNamespace::Profile,
            new JsonDecodingHydrator(),
            $this->checkForNotFoundStatusFetchErrorHandler(),
            $regionAndLocale
        );

        if ($characterStatus instanceof CharacterStatus) {
            return $characterStatus;
        }

        if (!$characterStatus->is_valid) {
            return CharacterStatus::Invalid;
        }

        if ($characterStatus->id !== $character->id) {
            return CharacterStatus::IdMismatch;
        }

        return CharacterStatus::Valid;
    }

    private function checkForNotFoundStatusFetchErrorHandler() : FetchErrorHandler {
        $errorHandler = new BlizzardErrorMappingExceptionThrowingFetchErrorHandler(
            UnableToFetchCharacterStatus::fromBlizzardError(...)
        );
        return new class($errorHandler) implements FetchErrorHandler {

            public function __construct(
                private readonly FetchErrorHandler $fetchErrorHandler,
            ) {}

            public function handle(int $status, string $responseBody) : ?object {
                if ($status === HttpStatus::NOT_FOUND) {
                    return CharacterStatus::NotFound;
                }

                return $this->fetchErrorHandler->handle($status, $responseBody);
            }
        };
    }

}
