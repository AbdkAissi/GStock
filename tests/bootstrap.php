<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

// Charger les variables d'environnement pour le mode test
if (file_exists(dirname(__DIR__) . '/.env.test')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env.test');
}
