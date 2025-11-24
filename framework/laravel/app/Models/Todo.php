<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    protected $fillable = [
        'title',
        'description',
        'completed',
        'due_date',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'due_date' => 'date',
    ];

    /**
     * Error: Accessing undefined property
     * PHPStan Level 0: undefined property
     */
    public function getPriorityLevel(): ?string
    {
        return $this->priority; // Property $priority does not exist
    }
}
