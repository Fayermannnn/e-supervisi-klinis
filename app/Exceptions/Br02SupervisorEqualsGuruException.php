<?php

namespace App\Exceptions;

class Br02SupervisorEqualsGuruException extends ApiException
{
    public function __construct()
    {
        parent::__construct('Supervisor tidak boleh sama dengan guru yang diobservasi.');
    }

    public function errorCode(): string
    {
        return 'BR02_SUPERVISOR_EQUALS_GURU';
    }

    public function statusCode(): int
    {
        return 422;
    }
}
