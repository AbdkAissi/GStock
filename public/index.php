<?php

use App\Kernel;

// Augmenter la limite de mémoire avant de charger l'application
ini_set('memory_limit', '512M'); // Ajuste cette valeur selon tes besoins

require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
