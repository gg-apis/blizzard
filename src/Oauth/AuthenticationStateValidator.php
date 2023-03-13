<?php declare(strict_types=1);

namespace GGApis\Blizzard\Oauth;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
interface AuthenticationStateValidator {

    public function isStateValid(string $state) : bool;

    public function markStateAsUsed(string $state) : void;

}