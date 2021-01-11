<?php

namespace Beaniegel\ImageStorage\Command;

use Beaniegel\ImageStorage\Config;
use Beaniegel\ImageStorage\ImageStorage;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class SaveImageCommand extends Command
{
    protected static $defaultName = 'save';

    private Config $config;
    private LoggerInterface $logger;

    public function __construct(Config $config, LoggerInterface $logger)
    {
        parent::__construct();

        $this->config = $config;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this->setDescription('save image')
            ->setHelp('saves the image located at path and outputs the new location')
            ->addArgument('path', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $imageStorage = new ImageStorage($this->config, $this->logger);

        try {
            $imagePath = $imageStorage->save($input->getArgument('path'));
            $output->writeln($imagePath);
        } catch (InvalidArgumentException $err) {
            $output->writeln('Error: '.$err->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
