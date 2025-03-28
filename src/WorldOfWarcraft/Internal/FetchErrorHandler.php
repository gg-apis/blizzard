<?php declare(strict_types=1);

namespace GGApis\Blizzard\WorldOfWarcraft\Internal;

use GGApis\Blizzard\Exception\Exception;

interface FetchErrorHandler {

    /**
     * Handle when fetching a resource results in a non-successful response code; either return an appropriate object
     * for the entity being fetched or throw an exception indicating what went wrong.
     *
     * @param int $status
     * @param string $responseBody
     * @return object|null
     * @throws Exception
     */
    public function handle(int $status, string $responseBody) : ?object;

}