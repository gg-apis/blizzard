<?php declare(strict_types=1);

namespace GGApis\Blizzard\Exception;

use GGApis\Blizzard\BlizzardError;

class UnableToFetchWorldOfWarcraftUserProfile extends Exception {

    public function __construct(
        public readonly BlizzardError $blizzardError,
        string $msg
    ) {
        parent::__construct($msg);

    }

    public static function fromBlizzardError(BlizzardError $blizzardError) : self {
        return new self(
            $blizzardError,
            sprintf('Blizzard responded with an invalid status code (%d) while fetching World of Warcraft user profile.', $blizzardError->code)
        );
    }

}