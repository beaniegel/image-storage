<?php

namespace Beaniegel\ImageStorage\Tests\Functional;

use Beaniegel\ImageStorage\Command\SaveImageCommand;
use Beaniegel\ImageStorage\Tests\Factory\ConfigFactory;
use Behat\Behat\Context\Context;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit\Framework\Assert;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    private CommandTester $commandTester;
    private string $source;
    private string $destination;

    /**
     * @BeforeScenario
     */
    public function beforeScenario()
    {
        $logger = new Logger('functional-test-storage');
        $config = ConfigFactory::create();
        $this->source = 'tests/tmp/';
        $this->destination = $config->getDestination();
        $app = new Application();

        $logger->pushHandler(new StreamHandler('test.log', Logger::WARNING));
        $app->add(new SaveImageCommand($config, $logger));

        $command = $app->find('save');
        $this->commandTester = new CommandTester($command);
        $this->createMissingDirectories();
    }

    /**
     * @Given /^"([^"]*)" is a local file$/
     */
    public function isALocalFile(string $path): void
    {
        Assert::assertTrue(copy(__DIR__.'/../assets/'.$path, $this->source.$path));
    }

    /**
     * @When /^I run the save command with argument "([^"]*)"$/
     */
    public function iRunTheSaveCommandWithArgument(string $path): void
    {
        $this->commandTester->execute([
            'path' => 'tests/tmp/'.$path,
        ]);
    }

    /**
     * @Then /^the application displays a valid image path$/
     */
    public function theApplicationDisplaysAValidImagePath(): void
    {
        $newImagePath = rtrim($this->commandTester->getDisplay());
        $fullImagePath = __DIR__.'/../storage/'.$newImagePath;
        Assert::assertFileExists($fullImagePath);
        $type = mime_content_type($fullImagePath);
        Assert::assertStringStartsWith('image/', $type);
    }

    /**
     * @Given /^the app's status code is (\d+)$/
     */
    public function theAppSStatusCodeIs(int $code): void
    {
        Assert::assertSame($this->commandTester->getStatusCode(), $code);
    }

    /**
     * @Given /^"([^"]*)" is a non existing image$/
     */
    public function isANonExistingImage($path): void
    {
        Assert::assertFileDoesNotExist($this->source.$path);
    }

    /**
     * @Then /^the application displays "([^"]*)"$/
     */
    public function theApplicationDisplays($output)
    {
        Assert::assertSame($output.PHP_EOL, $this->commandTester->getDisplay());
    }

    /**
     * @AfterScenario
     */
    public function afterScenario(): void
    {
        \array_map('unlink', glob($this->source.'*'));
        \array_map('unlink', glob($this->destination.'*'));
        rmdir($this->source);
        rmdir($this->destination);
    }

    private function createMissingDirectories()
    {
        $directories = [$this->source, $this->destination];

        foreach ($directories as $directory) {
            if (!file_exists($directory)) {
                mkdir($directory);
            }
        }
    }
}
