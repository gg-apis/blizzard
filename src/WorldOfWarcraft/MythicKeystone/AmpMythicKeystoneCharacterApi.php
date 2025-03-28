<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\MythicKeystone;

use CuyZ\Valinor\Mapper\Source\JsonSource;
use CuyZ\Valinor\Mapper\Source\Source;
use GGApis\Blizzard\Exception\UnableToFetchMythicKeystoneCharacterProfile;
use GGApis\Blizzard\Exception\UnableToFetchMythicKeystoneCharacterSeasonDetails;
use GGApis\Blizzard\Oauth\ClientAccessToken;
use GGApis\Blizzard\RegionAndLocale;
use GGApis\Blizzard\WorldOfWarcraft\BlizzardNamespace;
use GGApis\Blizzard\WorldOfWarcraft\CharacterIdentifier;
use GGApis\Blizzard\WorldOfWarcraft\Internal\AbstractBlizzardApi;
use GGApis\Blizzard\WorldOfWarcraft\Internal\BlizzardErrorMappingExceptionThrowingFetchErrorHandler;
use GGApis\Blizzard\WorldOfWarcraft\Internal\ValinorMappingHydrator;
use GGApis\Blizzard\WorldOfWarcraft\Internal\ValinorSourceProvider;

class AmpMythicKeystoneCharacterApi extends AbstractBlizzardApi implements MythicKeystoneCharacterApi {

    public function fetchMythicKeystoneCharacterProfile(
        ClientAccessToken $token,
        CharacterIdentifier $character,
        RegionAndLocale $regionAndLocale = null
    ) : MythicKeystoneCharacterProfile {
        $path = sprintf(
            '/profile/wow/character/%s/%s/mythic-keystone-profile',
            $character->getRealmSlug(),
            $character->getLowercaseName()
        );

        $resource = $this->processFetchResourceRequest(
            $token,
            $path,
            BlizzardNamespace::Profile,
            new ValinorMappingHydrator(
                $this->simpleMapper(),
                $this->characterProfileSourceProvider($character),
                MythicKeystoneCharacterProfile::class,
            ),
            new BlizzardErrorMappingExceptionThrowingFetchErrorHandler(
                UnableToFetchMythicKeystoneCharacterProfile::fromBlizzardError(...)
            ),
            $regionAndLocale
        );

        assert($resource instanceof MythicKeystoneCharacterProfile);
        return $resource;
    }

    private function characterProfileSourceProvider(CharacterIdentifier $character) : ValinorSourceProvider {
        return new class($character) implements ValinorSourceProvider {

            public function __construct(
                private readonly CharacterIdentifier $character
            ) {}

            public function source(string $body) : Source {
                return Source::iterable($this->transformedSource($body))
                    ->map(['current_mythic_rating' => 'rating'])
                    ->camelCaseKeys();
            }

            private function transformedSource(string $body) : array {
                $source = new JsonSource($body);
                $return = [];
                $rawSource = iterator_to_array($source);
                foreach ($rawSource as $key => $val) {
                    if ($key === 'current_period') {
                        $return['currentPeriodId'] = $val['period']['id'];
                        continue;
                    }

                    if ($key === 'seasons') {
                        $seasonIds = [];
                        foreach ($val as $season) {
                            $seasonIds[] = $season['id'];
                        }
                        $return['seasonIds'] = $seasonIds;
                        continue;
                    }

                    if ($key === 'character') {
                        $return['character'] = $this->character;
                        continue;
                    }
                    $return[$key] = $val;
                }

                if (!array_key_exists('current_mythic_rating', $return)) {
                    $return['current_mythic_rating'] = null;
                }

                if (!array_key_exists('seasonIds', $return)) {
                    $return['seasonIds'] = [];
                }

                return $return;
            }
        };
    }

    public function fetchMythicKeystoneCharacterSeasonDetails(
        ClientAccessToken $token,
        CharacterIdentifier $character,
        int $seasonId,
        RegionAndLocale $regionAndLocale = null
    ) : MythicKeystoneCharacterSeasonDetails {
        $path = sprintf(
            '/profile/wow/character/%s/%s/mythic-keystone-profile/season/9',
            $character->getRealmSlug(),
            $character->getLowercaseName()
        );

        $resource = $this->processFetchResourceRequest(
            $token,
            $path,
            BlizzardNamespace::Profile,
            new ValinorMappingHydrator(
                $this->simpleMapper(),
                $this->characterSeasonDetailsSourceProvider($character),
                MythicKeystoneCharacterSeasonDetails::class
            ),
            new BlizzardErrorMappingExceptionThrowingFetchErrorHandler(
                UnableToFetchMythicKeystoneCharacterSeasonDetails::fromBlizzardError(...)
            ),
            $regionAndLocale
        );

        assert($resource instanceof MythicKeystoneCharacterSeasonDetails);
        return $resource;
    }

    private function characterSeasonDetailsSourceProvider(CharacterIdentifier $character) : ValinorSourceProvider {
        return new class($character) implements ValinorSourceProvider {
            public function __construct(
                private readonly CharacterIdentifier $character,
            ) {}

            public function source(string $body) : Source {
                return Source::iterable(
                    $this->transformedSource($body)
                )->map([
                    'mythic_rating' => 'rating' ,
                    'best_runs.*.completed_timestamp' => 'completedAt',
                    'best_runs.*.keystone_affixes' => 'affixes',
                    'best_runs.*.mythic_rating' => 'rating'
                ])->camelCaseKeys();
            }

            private function transformedSource(string $body) : array {
                $source = new JsonSource($body);
                $return = [];
                $rawSource = iterator_to_array($source);
                foreach ($source as $key => $val) {
                    if ($key === 'character') {
                        $return['character'] = $this->character;
                        continue;
                    }

                    if ($key === 'season') {
                        $return['season_id'] = $rawSource[$key]['id'];
                        continue;
                    }

                    if ($key === 'best_runs') {
                        $return['best_runs'] = $this->transformBestRuns($val);
                        continue;
                    }

                    $return[$key] = $val;
                }

                return $return;
            }

            private function transformBestRuns(array $bestRuns) : array {
                $processedRuns = [];
                foreach ($bestRuns as $run) {
                    $bestRun = [];
                    foreach ($run as $key => $val) {
                        if ($key === 'duration') {
                            $bestRun['duration'] = sprintf('PT%dS', $val);
                            continue;
                        }

                        if ($key === 'members') {
                            $bestRun['members'] = $this->transformMembers($val);
                            continue;
                        }

                        $bestRun[$key] = $val;
                    }
                    $processedRuns[] = $bestRun;
                }
                return $processedRuns;
            }

            private function transformMembers(array $members) : array {
                $processedMembers = [];
                foreach ($members as $member) {
                    $processingMember = [];
                    foreach ($member as $key => $val) {
                        if ($key === 'character') {
                            $processingMember['id'] = $val['id'];
                            $processingMember['name'] = $val['name'];
                            $processingMember['realm_id'] = $val['realm']['id'];
                            continue;
                        }

                        $processingMember[$key] = $val;
                    }
                    $processedMembers[] = $processingMember;
                }
                return $processedMembers;
            }
        };
    }

}
