<?php

namespace App;

class Calculator
{
    /**
     * Add two numbers
     */
    public function add(int $a, int $b): int
    {
        return $a + $b;
    }

    /**
     * Subtract two numbers
     * This has a type error: should return int but returns string
     */
    public function subtract(int $a, int $b): int
    {
        return (string)($a - $b); // PHPStan will catch this type error
    }

    /**
     * Multiply with potential null issue
     */
    public function multiply(?int $a, int $b): int
    {
        return $a * $b; // PHPStan will warn about potential null
    }

    /**
     * Divide with no validation
     */
    public function divide(int $a, int $b): float
    {
        return $a / $b; // Division by zero not handled
    }

    /**
     * New method with undefined variable error
     */
    public function modulo(int $a, int $b): int
    {
        // PHPStan Level 0: undefined variable
        return $result % $b;
    }

    /**
     * New method calling non-existent method
     */
    public function power(int $base, int $exponent): int
    {
        // PHPStan Level 0: undefined method
        return $this->calculatePower($base, $exponent);
    }

    /**
     * New method accessing undefined property
     */
    public function getLastResult(): int
    {
        // PHPStan Level 0: undefined property
        return $this->lastResult;
    }
}
