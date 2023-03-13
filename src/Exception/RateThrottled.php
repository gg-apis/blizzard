<?php declare(strict_types=1);

namespace GGApis\Blizzard\Exception;

use GGApis\Blizzard\BlizzardError;

class RateThrottled extends Exception {

    public function __construct(
        public readonly BlizzardError $blizzardError,
        string $message
    ) {
        parent::__construct($message);
    }

    public static function fromRequestThrottled(BlizzardError $blizzardError) : self {
        return new self(
            $blizzardError,
            'Blizzard has throttled requests. Please wait before additional requests are made.'
        );
    }


}