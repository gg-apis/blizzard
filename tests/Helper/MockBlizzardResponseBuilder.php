<?php declare(strict_types=1);

namespace GGApis\Blizzard\Test\Helper;

use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Amp\Http\Status;

class MockBlizzardResponseBuilder {

    private function __construct() {}

    public static function fromJsonResponse(
        Request $request,
        int $status,
        array|string $body,
        /** @var array<string, string|list<string>> $headers */
        array $headers = []
    ) : Response {
        $responseBody = is_array($body) ? json_encode($body, flags: JSON_THROW_ON_ERROR) : $body;
        $response = new Response(
            '1.1',
            $status,
            null,
            ['Content-Type' => 'application/json;charset=UTF-8', 'Content-Length' => (string) strlen($responseBody)],
            $responseBody,
            $request
        );

        foreach ($headers as $name => $val) {
            $response->addHeader($name, $val);
        }

        return $response;
    }

    public static function fromNotModifiedResponse(Request $request) : Response {
        return new Response(
            '1.1',
            Status::NOT_MODIFIED,
            null,
            [],
            null,
            $request
        );
    }

    public static function fromNotJsonResponse(Request $request) : Response {
        return new Response(
            '1.1',
            Status::OK,
            null,
            ['Content-Type' => 'text/plain'],
            'Something that happened we did not expect that caused the server to respond with plain text.',
            $request
        );
    }

}
