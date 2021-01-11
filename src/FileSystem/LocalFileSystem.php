<?php

namespace Beaniegel\ImageStorage\FileSystem;

use Psr\Log\LoggerInterface;

final class LocalFileSystem implements FileSystem
{
    private LoggerInterface $logger;
    private string $destinationDirectory;

    public function __construct(string $destinationDirectory, LoggerInterface $logger)
    {
        $this->destinationDirectory = $destinationDirectory;
        $this->logger = $logger;
    }

    public function fileExists(string $path): bool
    {
        return file_exists($this->destinationDirectory.$path);
    }

    public function write(string $targetPath, string $filePath): string
    {
        rename($filePath, $this->destinationDirectory.$targetPath);
        $this->logger->info('new image added: '.$targetPath);

        return $targetPath;
    }

    public function read(string $path): string
    {
        return file_get_contents($this->destinationDirectory.$path);
    }

    public function delete(string $filePath): void
    {
        unlink($this->destinationDirectory.$filePath);
        $this->logger->info('image deleted: '.$filePath);
    }
}
