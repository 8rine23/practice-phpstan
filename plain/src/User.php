<?php

namespace App;

class User
{
    /** @var string */
    private $name;
    /** @var int */
    private $age;
    /** @var string|null */
    private $email;

    public function __construct(string $name, int $age, ?string $email = null)
    {
        $this->name = $name;
        $this->age = $age;
        $this->email = $email;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    /**
     * This method has a type inconsistency
     */
    public function getEmail(): string
    {
        return $this->email; // PHPStan will catch this - nullable string returned as string
    }

    /**
     * Method with undefined property access
     */
    public function getAddress(): string
    {
        return $this->address; // PHPStan will catch this - undefined property
    }

    /**
     * Method calling non-existent method
     */
    public function printInfo(): void
    {
        echo $this->formatInfo(); // PHPStan will catch this - method doesn't exist
    }
}
