<?php

declare(strict_types=1);

namespace Graywings\PhpDockerTemplate\Tests\Features;

use PHPUnit\Framework\TestCase;

class indexTest extends TestCase
{
    /**
     * @coversNothing
     * @return void
     */
    public function testIndex(): void
    {
        $this->expectOutputString('Hi! Thank you use it.');
        require realpath(__DIR__ . '/../../bin/index.php');
    }
}
