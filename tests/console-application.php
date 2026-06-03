<?php

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

(new Dotenv())->bootEnv(__DIR__ . '/../.env');

/** @var string $env */
$env = $_SERVER['APP_ENV'] ?? 'dev';

$kernel = new Kernel($env, (bool) $_SERVER['APP_DEBUG']);

return new Application($kernel);
