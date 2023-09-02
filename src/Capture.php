<?php

declare(strict_types=1);

namespace Graywings\ArrayCapture;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use stdClass;
use TypeError;

/**
 * @template T of object
 */
class Capture
{
    /**
     * @var class-string<T> $className
     */
    private string $className;

    /**
     * @var ReflectionClass<T> $reflectionClass
     */
    private ReflectionClass $reflectionClass;

    /**
     * @param class-string<T> $className
     */
    public function __construct(string $className)
    {
        if (class_exists($className)) {
            $reflectionClass = new ReflectionClass($className);
            $attributes = $reflectionClass->getAttributes(Capturable::class);
            if ($attributes) {
                $this->className = $className;
                $this->reflectionClass = $reflectionClass;
            } else {
                throw new InvalidArgumentException(
                    sprintf('Class %s is not capturable.', $className)
                );
            }
        } else {
            throw new InvalidArgumentException(
                sprintf('Class %s does not exist.', $className)
            );
        }
    }

    /**
     * @param mixed[]|stdClass $target
     * @return T
     */
    public function capture(
        stdClass|array $target
    ): mixed
    {
        $arguments = $this->buildArguments($this->reflectionClass, $target);
        try {
            /**
             * @var T
             */
            return $this->reflectionClass->newInstanceArgs($arguments);
        } catch (ReflectionException|TypeError $e) {
            throw new InvalidArgumentException(
                sprintf('Failed to capture %s.', $this->className),
                0,
                $e
            );
        }
    }

    /**
     * @param ReflectionClass<T> $reflectionClass
     * @param stdClass|mixed[] $target
     * @return mixed[]
     */
    private function buildArguments(
        ReflectionClass $reflectionClass,
        stdClass|array  $target
    ): array
    {
        $properties = $reflectionClass->getProperties();
        /**
         * @var mixed[] $arguments
         */
        $arguments = [];
        /**
         * TODO: Union and Intersection type is not supported yet.
         */
        foreach ($properties as $property) {
            if ($property->getAttributes(Capturable::class)) {
                $propertyName = $property->getName();
                $type = $property->getType();
                if ($type === null) {
                    throw new InvalidArgumentException(
                        sprintf('Property %s does not have type.', $propertyName)
                    );
                }
                if (get_class($type) === ReflectionNamedType::class) {
                    /**
                     * $arguments are mixed[] but Psalm thinks it is typed array.
                     * @psalm-suppress MixedAssignment
                     */
                    $arguments[] = self::buildArgument($type, $target, $propertyName);
                } else {
                    throw new InvalidArgumentException(
                        sprintf('Type %s is not supported yet.', get_class($type))
                    );
                }
            }
        }
        return $arguments;
    }

    /**
     * @param ReflectionNamedType $type
     * @param mixed[]|stdClass $target
     * @param string $propertyName
     * @return mixed
     */
    private function buildArgument(
        ReflectionNamedType $type,
        array|stdClass      $target,
        string              $propertyName
    ): mixed
    {
        /**
         * @var class-string|'array'|'string'|'int'|'float'|'bool'|'object'|'callable'|'resource' $typeName
         */
        $typeName = $type->getName();
        if (
            $typeName === 'array' ||
            $typeName === 'string' ||
            $typeName === 'int' ||
            $typeName === 'float' ||
            $typeName === 'bool' ||
            $typeName === 'object' ||
            $typeName === 'callable' ||
            $typeName === 'resource'
        ) {
            return self::getPropertyValue($target, $propertyName);
        } else {
            /**
             * @var mixed $propertyValue
             */
            $propertyValue = $this->getPropertyValue($target, $propertyName);
            if (
                is_array($propertyValue) ||
                is_object($propertyValue) &&
                get_class($propertyValue) === stdClass::class
            ) {
                $capture = new Capture($typeName);
                return $capture->capture($propertyValue);
            } else {
                throw new InvalidArgumentException(
                    sprintf('Target property %s is not capturable.', $propertyName)
                );
            }
        }
    }

    /**
     * @param mixed[]|stdClass $target
     * @param string $propertyName
     * @return mixed
     */
    private function getPropertyValue(
        array|stdClass $target,
        string         $propertyName
    ): mixed
    {
        if (is_array($target)) {
            return $target[$propertyName];
        } else {
            return $target->$propertyName;
        }
    }
}
