<?php declare(strict_types=1);

namespace GGApis\Blizzard\Oauth;

use Amp\Cache\Cache;
use Amp\Http\Client\Form;
use Amp\Http\Client\HttpClient;
use Cspray\HttpRequestBuilder\RequestBuilder;
use CuyZ\Valinor\Mapper\Source\JsonSource;
use CuyZ\Valinor\Mapper\Source\Source;
use GGApis\Blizzard\ApiConfig;
use GGApis\Blizzard\Exception\InvalidAuthenticationState;
use GGApis\Blizzard\Exception\UnableToAuthenticate;
use GGApis\Blizzard\Exception\UnableToFetchUser;
use GGApis\Blizzard\Http\BasicAuthHeader;
use GGApis\Blizzard\Http\BearerTokenHeader;
use GGApis\Blizzard\WorldOfWarcraft\Internal\AbstractBlizzardApi;
use GGApis\Blizzard\WorldOfWarcraft\Internal\BlizzardErrorMappingExceptionThrowingFetchErrorHandler;
use IteratorAggregate;
use League\Uri\Components\Query;
use League\Uri\Http;
use Psr\Http\Message\UriInterface;
use Traversable;

final class AmpAuthenticationApi extends AbstractBlizzardApi implements AuthenticationApi {

    private readonly UriInterface $oauthUri;

    public function __construct(
        HttpClient $client,
        ApiConfig $apiConfig,
        Cache $cache,
    ) {
        parent::__construct(
            $client,
            $apiConfig,
            $cache,
        );
        $this->oauthUri = Http::new('https://oauth.battle.net');
    }

    public function createAuthorizeUri(string $state, array $scopes, UriInterface $redirectUri) : UriInterface {
        $scope = implode(' ', array_map(static fn(Scope $scope) => $scope->value, $scopes));
        return $this->oauthUri
            ->withPath('/authorize')
            ->withQuery(Query::fromVariable([
                'client_id' => $this->config->getClientId(),
                'scope' => $scope,
                'state' => $state,
                'redirect_uri' => (string) $redirectUri,
                'response_type' => 'code'
            ])->toString());
    }

    public function generateOauthAccessToken(
        AuthorizationParameters $authorizationParameters,
        AuthenticationStateValidator $stateValidator
    ) : OauthAccessToken {
        if (!$stateValidator->isStateValid($authorizationParameters->state)) {
            throw InvalidAuthenticationState::fromInvalidStateCreatingAccessToken();
        }
        $stateValidator->markStateAsUsed($authorizationParameters->state);

        $request = RequestBuilder::withHeader(
            'Authorization',
            BasicAuthHeader::fromUserInfo($this->config->getClientId(), $this->config->getClientSecret())->toString()
        )->withFormBody(
            $this->createFormFromAuthorizationParameters($authorizationParameters)
        )->post($this->oauthUri->withPath('/token'));

        $response = $this->client->request($request);

        $this->validateContentTypeIsJson($response);

        $status = $response->getStatus();
        $body = $response->getBody()->buffer();

        $this->validateRateThrottlingNotActive($status, $body);
        $this->validateIsSuccessfulResponse(
            $status,
            $body,
            new BlizzardErrorMappingExceptionThrowingFetchErrorHandler(UnableToAuthenticate::fromInvalidResponseCode(...))
        );

        return $this->simpleMapper()->map(
            OauthAccessToken::class,
            Source::iterable($this->getOauthAccessTokenSource($body))
                ->map(['scope' => 'scopes'])
                ->camelCaseKeys()
        );
    }

    private function createFormFromAuthorizationParameters(AuthorizationParameters $authorizationParameters) : Form {
        $form = new Form();
        $form->addField('redirect_uri', (string) $authorizationParameters->redirectUri);
        $form->addField(
            'scope',
            implode(' ', array_map(
                static fn(Scope $scope) => $scope->value,
                $authorizationParameters->scopes
            ))
        );
        $form->addField('grant_type', $authorizationParameters->grantType);
        $form->addField('code', $authorizationParameters->code);
        return $form;
    }

    public function generateClientAccessToken() : ClientAccessToken {
        $form = new Form();
        $form->addField('grant_type', 'client_credentials');
        $request = RequestBuilder::withHeader(
                'Authorization', BasicAuthHeader::fromUserInfo($this->config->getClientId(), $this->config->getClientSecret())->toString()
            )->withFormBody($form)
            ->post($this->oauthUri->withPath('/token'));
        $response = $this->client->request($request);

        return $this->simpleMapper()->map(
            ClientAccessToken::class,
            Source::json($response->getBody()->buffer())->camelCaseKeys()
        );
    }

    private function getOauthAccessTokenSource(string $json) : IteratorAggregate {
        return new class(new JsonSource($json)) implements IteratorAggregate {

            private readonly iterable $source;

            public function __construct(
                JsonSource $source
            ) {
                $this->source = $this->transformScopes($source);
            }

            private function transformScopes(JsonSource $source) : iterable {
                $transformed = [];
                foreach ($source as $key => $val) {
                    if ($key === 'scope') {
                        $val = array_map(
                            static fn(string $scope) => Scope::from($scope),
                            explode(' ', $val)
                        );
                    }
                    $transformed[$key] = $val;
                }
                return $transformed;
            }

            public function getIterator() : Traversable {
                yield from $this->source;
            }
        };

    }

    public function fetchUser(OauthAccessToken $accessToken) : User {
        $response = $this->client->request(
            RequestBuilder::withHeader(
                'Authorization', BearerTokenHeader::fromToken($accessToken->accessToken)->toString()
            )->get(
                $this->oauthUri->withPath('/userinfo')
            )
        );

        $this->validateContentTypeIsJson($response);

        $status = $response->getStatus();
        $body = $response->getBody()->buffer();

        $this->validateRateThrottlingNotActive($status, $body);
        $this->validateIsSuccessfulResponse(
            $status,
            $body,
            new BlizzardErrorMappingExceptionThrowingFetchErrorHandler(
                UnableToFetchUser::fromBlizzardError(...)
            )
        );

        return $this->simpleMapper()->map(User::class, Source::json($body));
    }

}