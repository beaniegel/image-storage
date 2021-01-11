<?php

namespace Beaniegel\ImageStorage\Tests\Factory;

use Beaniegel\ImageStorage\Config;

class ConfigFactory
{
    public static function create()
    {
        $config = include __DIR__.'/../../config.php';

        return new Config([
            'type' => $config['test'],
            'destination_dir' => $config['test_dest_dir'],
        ]);
    }
}
