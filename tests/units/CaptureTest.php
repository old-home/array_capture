<?php

declare(strict_types=1);

namespace Graywings\ArrayCapture\Tests\Units;

use Graywings\ArrayCapture\Capturable;
use Graywings\ArrayCapture\Capture;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;
use function Graywings\Exceptions\initHandler;

#[Capturable]
class People
{
    #[Capturable]
    private string $name;

    #[Capturable]
    private int $age;

    #[Capturable]
    private ?float $height;

    #[Capturable]
    private bool $isMale;

    #[Capturable]
    private array $hobbies;

    #[Capturable]
    private Address $address;

    public function __construct(
        string  $name,
        int     $age,
        ?float  $height,
        bool    $isMale,
        array   $hobbies,
        Address $address
    )
    {
        $this->name = $name;
        $this->age = $age;
        $this->height = $height;
        $this->isMale = $isMale;
        $this->hobbies = $hobbies;
        $this->address = $address;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function age(): int
    {
        return $this->age;
    }

    public function height(): ?float
    {
        return $this->height;
    }

    public function isMale(): bool
    {
        return $this->isMale;
    }

    public function hobbies(): array
    {
        return $this->hobbies;
    }

    public function address(): Address
    {
        return $this->address;
    }
}

#[Capturable]
class Address
{
    #[Capturable]
    private string $country;

    #[Capturable]
    private string $city;

    #[Capturable]
    private string $street;

    public function __construct(
        string $country,
        string $city,
        string $street
    )
    {
        $this->country = $country;
        $this->city = $city;
        $this->street = $street;
    }

    public function country(): string
    {
        return $this->country;
    }

    public function city(): string
    {
        return $this->city;
    }

    public function street(): string
    {
        return $this->street;
    }
}

class NotCapturable
{
}

#[Capturable]
class PropertyTypeNotSpecified
{
    #[Capturable]
    private $property;
}

#[Capturable]
class UnionCapturable
{
    #[Capturable]
    public stdClass|array $intersection;
}

/**
 * @covers \Graywings\ArrayCapture\Capture
 */
class CaptureTest extends TestCase
{
    /**
     * @return void
     */
    public function testCapture(): void
    {
        $peopleCapture = new Capture(People::class);
        $people = $peopleCapture->capture([
            'name' => 'John',
            'age' => '20',
            'height' => null,
            'isMale' => '1',
            'hobbies' => ['soccer', 'baseball'],
            'address' => [
                'country' => 'Japan',
                'city' => 'Tokyo',
                'street' => 'Shinjuku',
            ],
        ]);

        $this->assertSame('John', $people->name());
        $this->assertSame(20, $people->age());
        $this->assertSame(null, $people->height());
        $this->assertSame(true, $people->isMale());
        $this->assertSame(['soccer', 'baseball'], $people->hobbies());
        $this->assertSame('Japan', $people->address()->country());
        $this->assertSame('Tokyo', $people->address()->city());
        $this->assertSame('Shinjuku', $people->address()->street());
    }

    /**
     * @return void
     */
    public function testStdClassTargetCapture(): void
    {
        $addressStdClass = new stdClass();
        $addressStdClass->country = 'Japan';
        $addressStdClass->city = 'Tokyo';
        $addressStdClass->street = 'Shinjuku';
        $addressCapture = new Capture(Address::class);
        $address = $addressCapture->capture($addressStdClass);

        $this->assertSame('Japan', $address->country());
        $this->assertSame('Tokyo', $address->city());
        $this->assertSame('Shinjuku', $address->street());
    }

    /**
     * @return void
     */
    public function testNotExistClassCapture(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Class NotExistClass does not exist.');
        new Capture('NotExistClass');
    }

    /**
     * @return void
     */
    public function testNotCapturableClassCapture(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Class Graywings\ArrayCapture\Tests\Units\NotCapturable is not capturable.');
        new Capture(NotCapturable::class);
    }

    /**
     * @return void
     */
    public function testPropertyTypeNotSpecifiedClassCapture(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Property property does not have type.');
        $capture = new Capture(PropertyTypeNotSpecified::class);
        $capture->capture(['property' => 'value']);
    }

    /**
     * @return void
     */
    public function testUnionCapturableClassCapture(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Type ReflectionUnionType is not supported yet.');
        $capture = new Capture(UnionCapturable::class);
        $capture->capture(['union' => 'value']);
    }

    public function testCaptureTargetIsNotArrayAndStdClass(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Target property address is not capturable.');
        $capture = new Capture(People::class);
        $capture->capture([
            'name' => 'John',
            'age' => '20',
            'height' => null,
            'isMale' => '1',
            'hobbies' => ['soccer', 'baseball'],
            'address' => new Address(
                'Japan',
                'Tokyo',
                'Shinjuku'
            )
        ]);
    }

    /**
     * @return void
     */
    public function testTypeNotMatched(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Failed to capture Graywings\ArrayCapture\Tests\Units\People.');
        $capture = new Capture(People::class);
        $capture->capture([
            'name' => ['John'],
            'age' => '20',
            'height' => null,
            'isMale' => '1',
            'hobbies' => ['soccer', 'baseball'],
            'address' => [
                'country' => 'Japan',
                'city' => 'Tokyo',
                'street' => 'Shinjuku',
            ],
        ]);
    }
}
