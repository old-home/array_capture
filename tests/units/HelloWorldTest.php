<?php

declare(strict_types=1);

namespace Graywings\PhpDockerTemplate\Tests\Units;

use Graywings\PhpDockerTemplate\HelloWorld;
use PHPUnit\Framework\TestCase;

class HelloWorldTest extends TestCase
{
    /**
     * @covers \Graywings\PhpDockerTemplate\HelloWorld::hi
     * @return void
     */
    public function testHi(): void
    {
        $this->expectOutputString('Hi! Thank you use it.');
        $helloWorld = new HelloWorld();
        $helloWorld->hi();
    }
}
