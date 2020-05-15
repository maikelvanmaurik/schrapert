<?php

namespace Schrapert\Http\Cookies;

use DateTimeInterface;

interface CookieInterface
{
    public function isExpired() : bool;

    public function getMaxAge() : ?int;

    public function getDiscard() : ?bool;

    public function getExpires() : ?DateTimeInterface;

    public function isHttpOnly() : ?bool;

    public function getName() : ?string;

    public function setName(?string $name) : self;

    public function getDomain() : ?string;

    public function setDomain(?string $domain) : self;

    public function getPath() : ?string;

    public function setPath(?string $path) : self;

    public function getPort() : ?string;

    public function setPort(?string $port) : self;

    public function isSecure() : bool;

    public function getValue();

    public function setValue(?string $value) : self;

    public function getVersion() : ?int;

    public function setVersion(?int $version) : self;

    public function getSameSite() : ?string;

    public function toArray() : array;
}
