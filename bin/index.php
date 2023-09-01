<?php

declare(strict_types=1);

require_once __DIR__ . "/../vendor/autoload.php";

use Graywings\PhpDockerTemplate\HelloWorld;

$helloWorld = new HelloWorld();
$helloWorld->hi();
