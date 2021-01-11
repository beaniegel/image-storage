<?php

namespace Beaniegel\ImageStorage\Command;

use Beaniegel\ImageStorage\Config;
use Beaniegel\ImageStorage\ImageStorage;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class RetrieveImageCommand extends Command
{
    protected static $defaultName = 'retrieve';

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
        $this->setDescription('retrieve base64 encoded image')
            ->setHelp('retrieve image located at given path')
            ->addArgument('path', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $imageStorage = new ImageStorage($this->config, $this->logger);

        try{
            $image = $imageStorage->retrieve($input->getArgument('path'));
            $output->write($image->base64Encoded());
        } catch (\InvalidArgumentException $err) {
            $output->writeln('Error: '.$err->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
