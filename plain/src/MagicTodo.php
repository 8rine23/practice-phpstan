<?php

namespace App;

/**
 * Class demonstrating magic methods for PHPStan Level 1
 *
 * PHPStan Level 1 detects unknown magic methods and properties
 * on classes with __call and __get
 */
class MagicTodo
{
    private $data = [];

    /**
     * Magic getter
     */
    public function __get(string $name)
    {
        return $this->data[$name] ?? null;
    }

    /**
     * Magic setter
     */
    public function __set(string $name, $value): void
    {
        $this->data[$name] = $value;
    }

    /**
     * Magic method caller
     */
    public function __call(string $name, array $arguments)
    {
        if (strpos($name, 'get') === 0) {
            $property = lcfirst(substr($name, 3));
            return $this->data[$property] ?? null;
        }

        throw new \BadMethodCallException("Method $name does not exist");
    }

    /**
     * Error: Accessing unknown magic property
     * PHPStan Level 1: unknown magic property
     */
    public function demonstrateMagicProperty(): void
    {
        // PHPStan Level 1: will detect this as unknown property
        // because it's not defined in any PHPDoc
        echo $this->unknownProperty;
    }

    /**
     * Error: Calling unknown magic method
     * PHPStan Level 1: unknown magic method
     */
    public function demonstrateMagicMethod(): void
    {
        // PHPStan Level 1: will detect this as unknown method
        // even though __call handles it
        $this->unknownMethod();
    }
}
