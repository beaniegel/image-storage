<?php

declare(strict_types=1);

namespace Beaniegel\ImageStorage\FileSystem;

interface FileSystem
{
    /**
     * returns true if a file exists at $path location.
     */
    public function fileExists(string $path): bool;

    /**
     * writes file location at filepath to targetPath, returns the image's new location.
     */
    public function write(string $targetPath, string $filePath): string;

    /**
     * reads file located at $path, returns the file's raw content.
     */
    public function read(string $path): string;

    /**
     * deletes file located at $path.
     */
    public function delete(string $path): void;
}
