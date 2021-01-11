<?php

namespace Beaniegel\ImageStorage;

use Beaniegel\ImageStorage\FileSystem\FileSystem;
use Beaniegel\ImageStorage\FileSystem\LocalFileSystem;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

final class ImageStorage
{
    protected LoggerInterface $logger;
    private FileSystem $fileSystem;

    public function __construct(Config $config, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->fileSystem = $this->setFileSystem($config);
    }

    public function save(string $imagePath): string
    {
        $this->validateImage($imagePath);
        $targetPath = $this->generateTargetPath($imagePath);

        while ($this->fileSystem->fileExists($targetPath)) {
            $targetPath = $this->generateTargetPath($targetPath);
        }

        return $this->fileSystem->write($targetPath, $imagePath);
    }

    public function retrieve(string $path): Image
    {
        if (!$this->fileSystem->fileExists($path)) {
            $this->logger->alert('image not found in storage: '.$path);
            throw new InvalidArgumentException('image not found');
        }

        return new Image($this->fileSystem->read($path));
    }

    public function delete(string $fileName): void
    {
        if ($this->fileSystem->fileExists($fileName)) {
            $this->fileSystem->delete($fileName);
        } else {
            $this->logger->warning('no image to delete');
        }
    }

    private function setFileSystem(Config $config): FileSystem
    {
        switch ($config->getType()) {
            case 'local':
            case 'test':
                return new LocalFileSystem($config->getDestination(), $this->logger);
            case 'ftp':
                // return ftp implementation
            case 's3':
                // return s3 implementation
            default:
                throw new InvalidArgumentException('filesystem type not recognised, check config.php');
        }
    }

    private function generateTargetPath(string $path): string
    {
        $extension = pathinfo($path)['extension'];
        $uuid = Uuid::v4();

        return $uuid->toRfc4122().'.'.$extension;
    }

    private function validateImage(string $path): void
    {
        if (!file_exists(BASE_DIR.$path)) {
            $this->logger->alert('image not found: '.BASE_DIR.$path);
            throw new InvalidArgumentException('image not found');
        }

        $mimeContentType = mime_content_type(BASE_DIR.$path);
        $imageIsInvalid = 'image/' !== substr($mimeContentType, 0, 6);

        if ($imageIsInvalid) {
            $this->logger->alert('file type unsupported: '.$mimeContentType);
            throw new InvalidArgumentException('this file type is not supported');
        }
    }
}
