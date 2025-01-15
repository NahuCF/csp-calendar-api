<?php

namespace App\Services;

class IdentifierService
{
    public static function generate($length = 6)
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

        return substr(str_shuffle($characters), 0, $length);
    }
}
