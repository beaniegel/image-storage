<?php

declare(strict_types=1);

namespace Beaniegel\ImageStorage;

use InvalidArgumentException;

final class Config
{
    private array $config;

    public function __construct(array $config)
    {
        $this->validateKeys($config);
        $this->config = $config;
    }

    public function getType(): string
    {
        return $this->config['type'];
    }

    public function getDestination(): string
    {
        return $this->config['destination_dir'];
    }

    private function validateKeys(array $config)
    {
        $keys = ['type', 'destination_dir'];

        foreach ($keys as $key) {
            if (!\array_key_exists($key, $config)) {
                throw new InvalidArgumentException();
            }
        }
    }
}
