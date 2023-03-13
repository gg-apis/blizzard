<?php declare(strict_types=1);

namespace GGApis\Blizzard\Exception;

final class InvalidAuthenticationState extends Exception {

    public static function fromInvalidStateCreatingAuthorizationUri() : self {
        return new self('Provided an invalid state for generating authorization URI.');
    }

    public static function fromInvalidStateCreatingAccessToken() : self {
        $msg = <<<TEXT
The state provided is not valid! This is likely representative of a malicious 
attack. If you believe the provided state should be valid please review the 
GGApis\Blizzard\Oauth\AuthenticationStateValidator implementation.
TEXT;
        return new self($msg);
    }

}