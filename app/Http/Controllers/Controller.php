<?php

namespace App\Http\Controllers;

use Error;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Illuminate\Support\Facades\Storage;
use PgSql\Lob;

abstract class Controller
{
    protected function fileExists(string $disk, string $path): bool
    {
        return Storage::disk($disk)->exists($path);
    }

    protected function moveFile(string $currentPath, string $newPath, string $disk)
    {
        if (!$currentPath) {
            throw new InvalidArgumentException('Current path is not provided.', 400);
        }

        if (!$newPath) {
            throw new InvalidArgumentException('New path is not provided.', 400);
        }

        if (!$disk) {
            throw new InvalidArgumentException('Disk is not provided.', 400);
        }

        if (!$this->fileExists($disk, $currentPath)) {
            throw new Error('The file at the current path does not exist.', 404);
        }

        $moved = Storage::disk($disk)->move($currentPath, $newPath);

        if (!$moved) {
            return false;
        }

        return $newPath;
    }

    protected function deleteFile(string $path, string $disk)
    {

        if (!$path) {
            throw new InvalidArgumentException('Disk is not provided.', 400);
        }

        if (!$disk) {
            throw new InvalidArgumentException('Disk is not provided.', 400);
        }

        if (!$this->fileExists($disk, $path)) {
            throw new Error('The provided path does not exist or is not a file.', 404);
        }

        return Storage::disk($disk)->delete($path);
    }

    protected function storeFile(UploadedFile $file, string $directory, string $disk, string|null $path = null): string|false
    {
        if (!$file) {
            throw new InvalidArgumentException('Image is not provided.', 400);
        }

        if (!$directory) {
            throw new InvalidArgumentException('Directory is not provided.', 400);
        }

        if (!$disk) {
            throw new InvalidArgumentException('Disk is not provided.', 400);
        }

        if ($path && $this->fileExists($disk, $path)) {
            $this->deleteFile($path, $disk);
        }

        return $file->store($directory, $disk);
    }
}
