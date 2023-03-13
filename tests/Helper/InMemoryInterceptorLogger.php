<?php declare(strict_types=1);

namespace GGApis\Blizzard\Test\Helper;

use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\Interceptor\TestingInterceptorLogger;
use Cspray\HttpClientTestInterceptor\Matcher\MatcherStrategyResult;

final class InMemoryInterceptorLogger implements TestingInterceptorLogger {

    public array $logs = [];

    public function log(Fixture $fixture, Request $request, MatcherStrategyResult $result) : void {
        $this->logs[] = [
            'fixture' => $fixture,
            'request' => $request,
            'result' => $result
        ];
    }
}