<?php

namespace FsKit\Contracts;

use FsKit\Artifacts\FsoType;

interface AttributesInterface
{
    public function path(): string;

    public function type(): FsoType;

    public function visibility(): null|string;

    public function dateCreated(): null|int;

    public function lastModified(): null|int;
}