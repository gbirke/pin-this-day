#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

use Birke\PinThisDay\Command\ImportCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new ImportCommand());
$application->run();