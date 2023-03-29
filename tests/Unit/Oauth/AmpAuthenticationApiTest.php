<?php declare(strict_types=1);

namespace GGApis\Blizzard\Test\Unit\Oauth;

use Amp\Cache\LocalCache;
use Amp\Http\Client\HttpClient;
use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Amp\Http\HttpStatus;
use Cspray\HttpClientTestInterceptor\HttpMockAwareTestTrait;
use Cspray\HttpRequestBuilder\RequestBuilder;
use CuyZ\Valinor\MapperBuilder;
use GGApis\Blizzard\ApiConfig;
use GGApis\Blizzard\BlizzardError;
use GGApis\Blizzard\Exception\Exception;
use GGApis\Blizzard\Exception\InvalidAuthenticationState;
use GGApis\Blizzard\Exception\InvalidContentType;
use GGApis\Blizzard\Exception\RateThrottled;
use GGApis\Blizzard\Exception\UnableToAuthenticate;
use GGApis\Blizzard\Exception\UnableToFetchUser;
use GGApis\Blizzard\Http\BasicAuthHeader;
use GGApis\Blizzard\Http\BearerTokenHeader;
use GGApis\Blizzard\Oauth\AmpAuthenticationApi;
use GGApis\Blizzard\Oauth\ClientAccessToken;
use GGApis\Blizzard\Oauth\OauthAccessToken;
use GGApis\Blizzard\Oauth\AuthenticationStateValidator;
use GGApis\Blizzard\Oauth\AuthorizationParameters;
use GGApis\Blizzard\Oauth\Scope;
use GGApis\Blizzard\Oauth\User;
use GGApis\Blizzard\Test\Helper\MockApiConfig;
use GGApis\Blizzard\Test\Helper\MockBlizzardResponseBuilder;
use GGApis\Blizzard\WorldOfWarcraft\Internal\AbstractBlizzardApi;
use League\Uri\Components\Query;
use League\Uri\Http;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[
    CoversClass(AmpAuthenticationApi::class),
    CoversClass(BlizzardError::class),
    CoversClass(Exception::class),
    CoversClass(UnableToAuthenticate::class),
    CoversClass(BasicAuthHeader::class),
    CoversClass(AuthorizationParameters::class),
    CoversClass(RateThrottled::class),
    CoversClass(BearerTokenHeader::class),
    CoversClass(OauthAccessToken::class),
    CoversClass(User::class),
    CoversClass(InvalidAuthenticationState::class),
    CoversClass(UnableToFetchUser::class),
    CoversClass(InvalidContentType::class),
    CoversClass(AbstractBlizzardApi::class),
    CoversClass(ClientAccessToken::class),
]
class AmpAuthenticationApiTest extends TestCase {

    use HttpMockAwareTestTrait;

    private HttpClient $httpClient;

    private ApiConfig $apiConfig;

    private AuthenticationStateValidator $stateValidator;

    private AmpAuthenticationApi $subject;

    protected function setUp() : void {
        $this->httpClient = (new HttpClientBuilder())->intercept($this->getMockingInterceptor())->build();
        $this->stateValidator = $this->getMockBuilder(AuthenticationStateValidator::class)->getMock();
        $this->apiConfig = new MockApiConfig();
        $this->subject = new AmpAuthenticationApi(
            $this->httpClient,
            $this->apiConfig,
            new LocalCache(),
            (new MapperBuilder())->mapper(),
        );
    }

    protected function assertPostConditions() : void {
        $this->validateHttpMocks();
    }

    private function getAccessTokenRequest() : Request {
        return RequestBuilder::withFormBody([
            'redirect_uri' => 'http://localhost/example/redirect',
            'scope' => 'openid wow.profile',
            'grant_type' => 'authorization_code',
            'code' => 'known-code'
        ])->addHeaders([
            'Authorization' => 'Basic ' . base64_encode('known-client-id:known-client-secret')
        ])->post('https://oauth.battle.net/token');
    }

    private function getMockResponse(Request $request, int $statusCode, array $body) : Response {
        return MockBlizzardResponseBuilder::fromJsonResponse($request, $statusCode, $body);
    }

    public function testCreateAuthorizeUriReturnsAppropriateUri() : void {
        $actual = $this->subject->createAuthorizeUri(
            'known-state',
            [Scope::OpenId, Scope::WowProfile],
            Http::createFromString('http://localhost/example/redirect')
        );

        self::assertSame('https', $actual->getScheme());
        self::assertSame('oauth.battle.net', $actual->getHost());
        self::assertSame('/authorize', $actual->getPath());
        self::assertSame(
            Query::createFromParams([
                'client_id' => 'known-client-id',
                'scope' => 'openid wow.profile',
                'state' => 'known-state',
                'redirect_uri' => 'http://localhost/example/redirect',
                'response_type' => 'code'
            ])->toString(),
            $actual->getQuery()
        );
    }

    public function testGenerateAccessTokenWithValidCodeAndState() : void {
        $this->httpMock()
            ->onRequest($request = $this->getAccessTokenRequest())
            ->returnResponse($this->getMockResponse($request, HttpStatus::OK, [
                'access_token' => 'access-token',
                'token_type' => 'bearer',
                'expires_in' => 2500,
                'scope' => 'openid wow.profile'
            ]));

        $this->stateValidator->expects($this->once())
            ->method('isStateValid')
            ->with('known-state')
            ->willReturn(true);

        $this->stateValidator->expects($this->once())
            ->method('markStateAsUsed')
            ->with('known-state');

        $accessToken = $this->subject->generateOauthAccessToken(
            new AuthorizationParameters('known-code', 'known-state', [Scope::OpenId, Scope::WowProfile], Http::createFromString('http://localhost/example/redirect')),
            $this->stateValidator
        );

        self::assertSame('access-token', $accessToken->accessToken);
        self::assertSame(2500, $accessToken->expiresIn);
        self::assertSame('bearer', $accessToken->tokenType);
        self::assertSame([Scope::OpenId, Scope::WowProfile], $accessToken->scopes);
    }

    public function testScopesRespectedFromBlizzardResponse() : void {
        $this->httpMock()
            ->onRequest($request = $this->getAccessTokenRequest())
            ->returnResponse($this->getMockResponse($request, HttpStatus::OK, [
                'access_token' => 'known-access-token',
                'token_type' => 'bearer',
                'expires_in' => 4000,
                'scope' => 'openid'
            ]));

        $this->stateValidator->expects($this->once())
            ->method('isStateValid')
            ->with('known-state')
            ->willReturn(true);

        $this->stateValidator->expects($this->once())
            ->method('markStateAsUsed')
            ->with('known-state');

        $accessToken =  $this->subject->generateOauthAccessToken(
            new AuthorizationParameters('known-code', 'known-state', [Scope::OpenId, Scope::WowProfile], Http::createFromString('http://localhost/example/redirect')),
            $this->stateValidator
        );

        self::assertSame('known-access-token', $accessToken->accessToken);
        self::assertSame(4000, $accessToken->expiresIn);
        self::assertSame('bearer', $accessToken->tokenType);
        self::assertSame([Scope::OpenId], $accessToken->scopes);
    }

    public function testInvalidStateThrowsException() : void {
        $this->stateValidator->expects($this->once())
            ->method('isStateValid')
            ->with('known-state')
            ->willReturn(false);

        $this->stateValidator->expects($this->never())->method('markStateAsUsed');

        $expected = <<<TEXT
The state provided is not valid! This is likely representative of a malicious 
attack. If you believe the provided state should be valid please review the 
GGApis\Blizzard\Oauth\AuthenticationStateValidator implementation.
TEXT;

        $this->expectException(InvalidAuthenticationState::class);
        $this->expectExceptionMessage($expected);

        $this->subject->generateOauthAccessToken(
            new AuthorizationParameters('known-code', 'known-state', [Scope::OpenId, Scope::WowProfile], Http::createFromString('http://localhost/example/redirect')),
            $this->stateValidator
        );
    }

    public function testGenerateAccessTokenWithInvalidResponseFromBlizzardThrowsException() : void {
        $this->httpMock()
            ->onRequest($request = $this->getAccessTokenRequest())
            ->returnResponse($this->getMockResponse($request, HttpStatus::UNAUTHORIZED, [
                'code' => 401,
                'type' => 'BLZWEBAPI00000401',
                'detail' => 'Unauthorized'
            ]));

        $this->stateValidator->expects($this->once())
            ->method('isStateValid')
            ->with('known-state')
            ->willReturn(true);

        $this->stateValidator->expects($this->once())
            ->method('markStateAsUsed')
            ->with('known-state');

        $this->expectException(UnableToAuthenticate::class);
        $this->expectExceptionMessage(
            'Blizzard responded with an invalid status code (401) while attempting to authenticate.'
        );

        $this->subject->generateOauthAccessToken(
            new AuthorizationParameters('known-code', 'known-state', [Scope::OpenId, Scope::WowProfile], Http::createFromString('http://localhost/example/redirect')),
            $this->stateValidator
        );
    }

    public function testGenerateAccessTokenWithRateThrottledFromBlizzardThrowsException() : void {
        $this->httpMock()
            ->onRequest($request = $this->getAccessTokenRequest())
            ->returnResponse($this->getMockResponse($request, HttpStatus::TOO_MANY_REQUESTS, [
                'code' => 429,
                'type' => 'BLZWEBAPI00000429',
                'detail' => 'Too Many Requests'
            ]));

        $this->stateValidator->expects($this->once())
            ->method('isStateValid')
            ->with('known-state')
            ->willReturn(true);

        $this->stateValidator->expects($this->once())
            ->method('markStateAsUsed')
            ->with('known-state');

        $this->expectException(RateThrottled::class);
        $this->expectExceptionMessage(
            'Blizzard has throttled requests. Please wait before additional requests are made.'
        );

        $this->subject->generateOauthAccessToken(
            new AuthorizationParameters('known-code', 'known-state', [Scope::OpenId, Scope::WowProfile], Http::createFromString('http://localhost/example/redirect')),
            $this->stateValidator
        );
    }

    public function testGenerateAccessTokenRespondsWithoutJsonThrowsException() : void {
        $responseBody = 'Body from server down or bad response';
        $this->httpMock()
            ->onRequest($request = $this->getAccessTokenRequest())
            ->returnResponse(
                new Response(
                    '1.1',
                    HttpStatus::OK,
                    null,
                    ['Content-Type' => 'text/plain', 'Content-Length' => (string) strlen($responseBody)],
                    $responseBody,
                    $request
                )
            );

        $this->stateValidator->expects($this->once())
            ->method('isStateValid')
            ->with('known-state')
            ->willReturn(true);

        $this->stateValidator->expects($this->once())
            ->method('markStateAsUsed')
            ->with('known-state');

        $this->expectException(InvalidContentType::class);
        $this->expectExceptionMessage('Expected Content-Type of "application/json" but received "text/plain".');
        $this->subject->generateOauthAccessToken(
            new AuthorizationParameters('known-code', 'known-state', [Scope::OpenId, Scope::WowProfile], Http::createFromString('http://localhost/example/redirect')),
            $this->stateValidator
        );
    }

    public function testFetchUserInfoParsesResponseCorrectly() : void {
        $request = RequestBuilder::withHeader('Authorization', 'Bearer access-token')
            ->get('https://oauth.battle.net/userinfo');
        $this->httpMock()
            ->onRequest($request)
            ->returnResponse($this->getMockResponse($request, HttpStatus::OK, [
                'sub' => '420',
                'id' => $id = random_int(0, PHP_INT_MAX),
                'battletag' => $battleTag = bin2hex(random_bytes(4))
            ]));

        $user = $this->subject->fetchUser(
            new OauthAccessToken('access-token', 'bearer', 400, [Scope::OpenId, Scope::WowProfile])
        );

        self::assertSame($id, $user->id);
        self::assertSame($battleTag, $user->battletag);
    }

    public function testFetchUserInfoReturnsForbiddenThrowsException() : void {
        $request = RequestBuilder::withHeader('Authorization', 'Bearer access-token')
            ->get('https://oauth.battle.net/userinfo');
        $this->httpMock()
            ->onRequest($request)
            ->returnResponse($this->getMockResponse($request, HttpStatus::FORBIDDEN, [
                'code' => HttpStatus::FORBIDDEN,
                'type' => 'BLZWEBAPI00000403',
                'detail' => 'Forbidden',
            ]));

        $this->expectException(UnableToFetchUser::class);
        $this->expectExceptionMessage(
            'Blizzard responded with an invalid status code (403) while fetching an authenticated user.'
        );

        $this->subject->fetchUser(
            new OauthAccessToken('access-token', 'bearer', 400, [Scope::OpenId, Scope::WowProfile])
        );
    }

    public function testFetchUserInfoRateThrottledThrowsException() : void {
        $request = RequestBuilder::withHeader('Authorization', 'Bearer access-token')
            ->get('https://oauth.battle.net/userinfo');

        $this->httpMock()
            ->onRequest($request)
            ->returnResponse($this->getMockResponse($request, HttpStatus::TOO_MANY_REQUESTS, [
                'code' => 429,
                'type' => 'BLZWEBAPI00000429',
                'detail' => 'Too Many Requests'
            ]));

        $this->expectException(RateThrottled::class);
        $this->expectExceptionMessage(
            'Blizzard has throttled requests. Please wait before additional requests are made.'
        );

        $this->subject->fetchUser(
            new OauthAccessToken('access-token', 'bearer', 400, [Scope::OpenId, Scope::WowProfile])
        );
    }

    public function testFetchUserInfoRespondsWithoutJsonThrowsException() : void {
        $request = RequestBuilder::withHeader('Authorization', 'Bearer access-token')
            ->get('https://oauth.battle.net/userinfo');
        $responseBody = 'Body from server down or bad response';
        $this->httpMock()
            ->onRequest($request)
            ->returnResponse(
                new Response(
                    '1.1',
                    HttpStatus::OK,
                    null,
                    ['Content-Type' => 'text/plain', 'Content-Length' => (string) strlen($responseBody)],
                    $responseBody,
                    $request
                )
            );

        $this->expectException(InvalidContentType::class);
        $this->expectExceptionMessage('Expected Content-Type of "application/json" but received "text/plain".');
        $this->subject->fetchUser(
            new OauthAccessToken('access-token', 'bearer', 6000, [Scope::OpenId, Scope::WowProfile])
        );
    }

    public function testGetClientAccessTokenValidClientIdAndSecretReturnsAccessToken() : void {
        $this->httpMock()
            ->onRequest(
                $request = RequestBuilder::withHeaders([
                    'Authorization' => BasicAuthHeader::fromUserInfo('known-client-id', 'known-client-secret')->toString()
                ])->withFormBody([
                    'grant_type' => 'client_credentials'
                ])->post('https://oauth.battle.net/token')
            )->returnResponse(MockBlizzardResponseBuilder::fromJsonResponse($request, HttpStatus::OK, [
                'access_token' => $expectedToken = bin2hex(random_bytes(8)),
                'token_type' => 'bearer',
                'expires_in' => 2500,
                'sub' => $expectedSub = bin2hex(random_bytes(8))
            ]));

        $token = $this->subject->generateClientAccessToken();

        self::assertSame($expectedToken, $token->accessToken);
        self::assertSame('bearer', $token->tokenType);
        self::assertSame(2500, $token->expiresIn);
        self::assertSame($expectedSub, $token->sub);
    }
}
