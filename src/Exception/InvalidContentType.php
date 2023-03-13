<?php declare(strict_types=1);

namespace GGApis\Blizzard\Exception;

final class InvalidContentType extends Exception {

    public static function fromInvalidContentType(string $contentType) : self {
        return new self(
            sprintf('Expected Content-Type of "application/json" but received "%s".', $contentType)
        );
    }

}