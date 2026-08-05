<?php

namespace App\Exceptions;

use Exception;

/**
 * Basis seluruh exception yang direspons via envelope API {data, meta, errors}.
 * Kode error dan status HTTP ditentukan oleh masing-masing subclass, satu
 * exception per kode error sesuai konvensi penamaan Dok 12 Coding Standards.
 */
abstract class ApiException extends Exception
{
    abstract public function errorCode(): string;

    abstract public function statusCode(): int;
}
