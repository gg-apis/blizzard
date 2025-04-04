<?php declare(strict_types=1);

namespace GGApis\Blizzard\Oauth;

use Psr\Http\Message\UriInterface;

interface AuthenticationApi {

    public function createAuthorizeUri(string $state, array $scopes, UriInterface $redirectUri) : UriInterface;

    public function generateOauthAccessToken(
        AuthorizationParameters $authorizationParameters,
        AuthenticationStateValidator $stateValidator
    ) : OauthAccessToken;

    public function generateClientAccessToken() : ClientAccessToken;

    public function fetchUser(OauthAccessToken $accessToken) : User;

}