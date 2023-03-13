<?php declare(strict_types=1);

namespace GGApis\Blizzard\Exception;

use GGApis\Blizzard\BlizzardError;
use Throwable;

class UnableToFetchMythicKeystoneCharacterProfile extends Exception {

    protected function __construct(
        public readonly BlizzardError $blizzardError,
        string $message
    ) {
        parent::__construct($message);
    }

    public static function fromBlizzardError(BlizzardError $blizzardError) {
        return new self(
            $blizzardError,
            sprintf(
                'Blizzard responded with an invalid status code (%d) while fetching Mythic Keystone character profile.',
                $blizzardError->code
            )
        );
    }

}