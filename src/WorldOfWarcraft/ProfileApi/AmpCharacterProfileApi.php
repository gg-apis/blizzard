<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\ProfileApi;

use Amp\Http\HttpStatus;
use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\HttpRequestBuilder\RequestBuilder;
use GGApis\Blizzard\Exception\UnableToFetchCharacterStatus;
use GGApis\Blizzard\Http\BearerTokenHeader;
use GGApis\Blizzard\Oauth\ClientAccessToken;
use GGApis\Blizzard\Region;
use GGApis\Blizzard\WorldOfWarcraft\BlizzardNamespace;
use GGApis\Blizzard\WorldOfWarcraft\Character;
use GGApis\Blizzard\WorldOfWarcraft\CharacterStatus;
use GGApis\Blizzard\WorldOfWarcraft\Internal\AbstractBlizzardApi;

#[Service]
class AmpCharacterProfileApi extends AbstractBlizzardApi  implements CharacterProfileApi {

    public function fetchCharacterStatus(ClientAccessToken $token, Character $character, Region $region = null) : CharacterStatus {
        $request = RequestBuilder::withHeaders([
            'Authorization' => BearerTokenHeader::fromToken($token->accessToken)->toString(),
            'Battlenet-Namespace' => BlizzardNamespace::Profile->toString($region ?? $this->config->getDefaultRegion())
        ])->get(
            $this->apiUriForRegion($region ?? $this->config->getDefaultRegion())
                ->withPath(sprintf(
                    '/profile/wow/character/%s/%s/status',
                    $character->realm->slug,
                    strtolower($character->name)
                ))
        );
        $response = $this->client->request($request);

        if ($response->getStatus() === HttpStatus::NOT_FOUND) {
            return CharacterStatus::NotFound;
        }

        $this->validateContentTypeIsJson($response);

        $body = $response->getBody()->buffer();

        $this->validateRateThrottlingNotActive($response->getStatus(), $body);
        $this->validateIsSuccessfulResponse($response->getStatus(), $body, UnableToFetchCharacterStatus::fromBlizzardError(...));

        $characterStatus = json_decode($body, true, flags: JSON_THROW_ON_ERROR);

        if (!$characterStatus['is_valid']) {
            return CharacterStatus::Invalid;
        }

        if ($characterStatus['id'] !== $character->id) {
            return CharacterStatus::IdMismatch;
        }

        return CharacterStatus::Valid;
    }

}
