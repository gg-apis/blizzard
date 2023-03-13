<?php declare(strict_types=1);

namespace GGApis\Blizzard\Oauth;

use Cspray\AnnotatedContainer\Attribute\Service;
use Psr\Http\Message\UriInterface;

#[Service]
interface AuthenticationApi {

    public function createAuthorizeUri(string $state, array $scopes) : UriInterface;

    public function generateOauthAccessToken(AuthorizationParameters $authorizationParameters) : OauthAccessToken;

    public function generateClientAccessToken() : ClientAccessToken;

    public function fetchUser(OauthAccessToken $accessToken) : User;

}