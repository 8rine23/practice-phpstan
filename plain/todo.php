#!/usr/bin/env php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\TodoRepository;
use App\TodoService;
use App\TodoCommand;

// Initialize the application
$dataFile = __DIR__ . '/data/todos.json';

// Create data directory if it doesn't exist
$dataDir = dirname($dataFile);
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

$repository = new TodoRepository($dataFile);
$service = new TodoService($repository);
$command = new TodoCommand($service);

// Run the command
$command->run($argv);
