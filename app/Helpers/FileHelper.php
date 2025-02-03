<?php

namespace App\Helpers;

class FileHelper
{
    public static function getRelativeFilePath($path)
    {
        return str_replace('http://localhost:8000/storage/', '', $path);
    }
}
