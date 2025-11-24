<?php

namespace App;

class Todo
{
    /** @var int */
    private $id;
    /** @var string */
    private $title;
    /** @var string */
    private $description;
    /** @var bool */
    private $completed;
    /** @var string|null */
    private $dueDate;

    public function __construct(int $id, string $title, string $description = '', bool $completed = false, ?string $dueDate = null)
    {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->completed = $completed;
        $this->dueDate = $dueDate;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function complete(): void
    {
        $this->completed = true;
    }

    public function getDueDate(): ?string
    {
        return $this->dueDate;
    }

    /**
     * Error: Accessing undefined property
     */
    public function getPriority(): string
    {
        return $this->priority; // PHPStan Level 0: undefined property
    }

    /**
     * Error: Calling undefined method
     */
    public function display(): void
    {
        echo $this->formatDisplay(); // PHPStan Level 0: undefined method
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'completed' => $this->completed,
            'due_date' => $this->dueDate,
        ];
    }
}
