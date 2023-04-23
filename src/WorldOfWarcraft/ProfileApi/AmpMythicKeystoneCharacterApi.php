<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\ProfileApi;

use Cspray\AnnotatedContainer\Attribute\Service;
use CuyZ\Valinor\Mapper\Source\JsonSource;
use CuyZ\Valinor\Mapper\Source\Source;
use GGApis\Blizzard\Exception\UnableToFetchMythicKeystoneCharacterProfile;
use GGApis\Blizzard\Exception\UnableToFetchMythicKeystoneCharacterSeasonDetails;
use GGApis\Blizzard\Oauth\ClientAccessToken;
use GGApis\Blizzard\RegionAndLocale;
use GGApis\Blizzard\WorldOfWarcraft\BlizzardNamespace;
use GGApis\Blizzard\WorldOfWarcraft\Character;
use GGApis\Blizzard\WorldOfWarcraft\CharacterIdentifier;
use GGApis\Blizzard\WorldOfWarcraft\Internal\AbstractBlizzardApi;
use GGApis\Blizzard\WorldOfWarcraft\MythicKeystoneCharacterProfile;
use GGApis\Blizzard\WorldOfWarcraft\MythicKeystoneCharacterSeasonDetails;
use IteratorAggregate;
use Traversable;

#[Service]
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
            fn(string $body) => $this->hydrateCharacterProfile($character, $body),
            UnableToFetchMythicKeystoneCharacterProfile::fromBlizzardError(...),
            $regionAndLocale
        );

        assert($resource instanceof MythicKeystoneCharacterProfile);
        return $resource;
    }

    private function hydrateCharacterProfile(CharacterIdentifier $character, string $body) : MythicKeystoneCharacterProfile {
        return $this->mapper->map(
            MythicKeystoneCharacterProfile::class,
            Source::iterable(
                $this->getMythicKeystoneCharacterProfileSource(
                    $character,
                    $body
                )
            )->map([
                'current_mythic_rating' => 'rating',
            ])->camelCaseKeys()
        );
    }

    private function getMythicKeystoneCharacterProfileSource(CharacterIdentifier $character, string $json) : IteratorAggregate {
        return new class($character, new JsonSource($json)) implements IteratorAggregate {

            private readonly iterable $source;

            public function __construct(
                private readonly Character $character,
                private readonly JsonSource $json
            ) {
                $this->source = $this->transformSource($this->json);
            }

            private function transformSource(JsonSource $source) : iterable {
                $return = [];
                $rawSource = iterator_to_array($source);
                foreach ($rawSource as $key => $val) {
                    if ($key === 'current_period') {
                        $return['currentPeriodId'] = $rawSource[$key]['period']['id'];
                        continue;
                    }

                    if ($key === 'seasons') {
                        $seasonIds = [];
                        foreach ($rawSource[$key] as $season) {
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
                return $return;
            }

            public function getIterator() : Traversable {
                yield from $this->source;
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
            fn(string $body) => $this->hydrateCharacterSeasonDetails($character, $body),
            UnableToFetchMythicKeystoneCharacterSeasonDetails::fromBlizzardError(...),
            $regionAndLocale
        );

        assert($resource instanceof MythicKeystoneCharacterSeasonDetails);
        return $resource;
    }

    private function hydrateCharacterSeasonDetails(CharacterIdentifier $character, string $body) : MythicKeystoneCharacterSeasonDetails {
        return $this->mapper->map(
            MythicKeystoneCharacterSeasonDetails::class,
            Source::iterable(
                $this->getMythicKeystoneCharacterSeasonDetailsSource($character, $body)
            )->map([
                'mythic_rating' => 'rating' ,
                'best_runs.*.completed_timestamp' => 'completedAt',
                'best_runs.*.keystone_affixes' => 'affixes',
                'best_runs.*.mythic_rating' => 'rating'
            ])->camelCaseKeys()
        );
    }

    private function getMythicKeystoneCharacterSeasonDetailsSource(CharacterIdentifier $character, string $body) : IteratorAggregate {
        return new class($character, new JsonSource($body)) implements IteratorAggregate {

            private readonly iterable $source;

            public function __construct(
                private readonly Character $character,
                JsonSource $source
            ) {
                $this->source = $this->transformSource($source);
            }

            private function transformSource(JsonSource $source) : iterable {
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

            public function getIterator() : Traversable {
                yield from $this->source;
            }
        };
    }

}
