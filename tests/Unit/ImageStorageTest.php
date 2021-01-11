<?php

declare(strict_types=1);

namespace Beaniegel\ImageStorage\Tests\Unit;

use Beaniegel\ImageStorage\ImageStorage;
use Beaniegel\ImageStorage\Tests\Factory\ConfigFactory;
use InvalidArgumentException;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

\define('BASE_DIR', \dirname(__DIR__, 2).'/');

/**
 * @covers \Beaniegel\ImageStorage\ImageStorage;
 */
final class ImageStorageTest extends TestCase
{
    private ImageStorage $imageStorage;
    private string $source;
    private string $destination;

    protected function setUp(): void
    {
        $config = ConfigFactory::create();
        $logger = New Logger('unit-test-storage');
        $logger->pushHandler(new StreamHandler(BASE_DIR.'test.log', Logger::WARNING));
        $this->imageStorage = new ImageStorage($config, $logger);
        $this->source = 'tests/tmp/';
        $this->destination = $config->getDestination();

        mkdir($this->source);
        mkdir($this->destination);
    }

    public function testSavingAValidImage(): void
    {
        $path = $this->createImagePath('valid_image.jpg');
        $image = file_get_contents($path);
        $storedPath = $this->imageStorage->save($path);
        $storedImage = file_get_contents($this->destination.$storedPath);

        Assert::assertFileDoesNotExist($path);
        Assert::assertfileExists($this->destination.$storedPath);
        Assert::assertSame($image, $storedImage);
    }

    public function testAttemptToSaveAnInvalidFileType(): void
    {
        $path = $this->createImagePath('invalid_file_type.txt');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('this file type is not supported');

        $this->imageStorage->save($path);
    }

    public function testAttemptToSaveAnNonExistentImage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('image not found');

        $this->imageStorage->save('non_existent_file.jpg');
    }

    public function testRetrieveAnExistingImage(): void
    {
        $path = $this->createImagePath('valid_image.jpg');
        $storedPath = $this->imageStorage->save($path);
        $retrievedImage = $this->imageStorage->retrieve($storedPath);
        $storedImage = file_get_contents($this->destination.$storedPath);

        Assert::assertSame($retrievedImage->raw(), $storedImage);
    }

    public function testAttemptToRetrieveANonExistingImage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('image not found');

        $this->imageStorage->retrieve('non_existent_file.jpg');
    }

    public function testDelete(): void
    {
        $path = $this->createImagePath('valid_image.jpg');
        $storedPath = $this->imageStorage->save($path);

        Assert::assertFileExists($this->destination.$storedPath);
        $this->imageStorage->delete($storedPath);
        Assert::assertFileDoesNotExist($this->destination.$storedPath);
    }

    private function createImagePath($assetName): string
    {
        $assetPath = BASE_DIR.'tests/assets/'.$assetName;
        $tmpPath = $this->source.$assetName;

        copy($assetPath, $tmpPath);

        return $tmpPath;
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->source.'*'));
        array_map('unlink', glob($this->destination.'*'));
        rmdir($this->source);
        rmdir($this->destination);
    }
}
