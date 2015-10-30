#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

use RabbitmqTest\Send;
use RabbitmqTest\Received;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new Send());
$application->add(new Received());
$application->run();
