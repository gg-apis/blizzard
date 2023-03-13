<?php declare(strict_types=1);

namespace GGApis\Blizzard\Exception;

use GGApis\Blizzard\BlizzardError;

final class UnableToAuthenticate extends Exception {


    protected function __construct(
        public readonly BlizzardError $blizzardError,
        string $message
    ) {
        parent::__construct($message);
    }

    public static function fromInvalidResponseCode(BlizzardError $blizzardError) : self {
        return new self(
            $blizzardError,
            sprintf('Blizzard responded with an invalid status code (%d) while attempting to authenticate.', $blizzardError->code)
        );
    }

}
