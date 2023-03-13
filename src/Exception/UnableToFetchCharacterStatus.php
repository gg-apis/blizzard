<?php declare(strict_types=1);

namespace GGApis\Blizzard\Exception;

use GGApis\Blizzard\BlizzardError;
use Throwable;

final class UnableToFetchCharacterStatus extends Exception {

    private function __construct(
        public readonly BlizzardError $blizzardError,
        string $message
    ) {
        parent::__construct($message);
    }


    public static function fromBlizzardError(BlizzardError $blizzardError) : self {
        return new self(
            $blizzardError,
            sprintf(
                'Blizzard responded with an invalid status code (%d) while fetching Character status.',
                $blizzardError->code
            )
        );
    }

}
