<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;

class ExportService
{
    public function export(Collection $todos): string
    {
        $output = "Todo Export\n";
        $output .= "===========\n\n";

        foreach ($todos as $todo) {
            $output .= "Title: {$todo->title}\n";
            $output .= "Description: {$todo->description}\n";
            $output .= "Status: " . ($todo->completed ? 'Completed' : 'Pending') . "\n";
            $output .= "---\n";
        }

        return $output;
    }

    /**
     * Error: Accessing undefined property
     * PHPStan Level 1: undefined property
     * (Public method for demonstration - not used)
     */
    public function getFormat(): string
    {
        // Intentional error: $this->format does not exist
        return $this->format;
    }
}
