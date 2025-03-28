<?php declare(strict_types=1);

namespace GGApis\Blizzard\Exception;

use GGApis\Blizzard\BlizzardError;

class UnableToFetchPetCollection extends Exception {

    public function __construct(
        public readonly BlizzardError $blizzardError,
        string $message
    ) {
        parent::__construct($message);
    }

    public static function fromBlizzardError(BlizzardError $blizzardError) : self {
        return new self(
            $blizzardError,
            sprintf(
                'Blizzard responded with an invalid status code (%s) while fetching pet collection summary.',
                $blizzardError->code
            )
        );
    }

}