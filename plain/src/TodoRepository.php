<?php

namespace App;

class TodoRepository
{
    /** @var string */
    private $dataFile;
    /** @var array */
    private $todos = [];

    public function __construct(string $dataFile = 'todos.json')
    {
        $this->dataFile = $dataFile;
        $this->load();
    }

    private function load(): void
    {
        if (file_exists($this->dataFile)) {
            $json = file_get_contents($this->dataFile);
            $data = json_decode($json, true);

            foreach ($data as $item) {
                $this->todos[] = new Todo(
                    $item['id'],
                    $item['title'],
                    $item['description'] ?? '',
                    $item['completed'] ?? false,
                    $item['due_date'] ?? null
                );
            }
        }
    }

    public function save(): void
    {
        $data = [];
        foreach ($this->todos as $todo) {
            $data[] = $todo->toArray();
        }

        file_put_contents($this->dataFile, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function findAll(): array
    {
        return $this->todos;
    }

    public function findById(int $id): ?Todo
    {
        foreach ($this->todos as $todo) {
            if ($todo->getId() === $id) {
                return $todo;
            }
        }

        return null;
    }

    /**
     * Error: Accessing undefined variable
     */
    public function add(Todo $todo): void
    {
        $this->todos[] = $todo;
        $this->save();

        // PHPStan Level 0: undefined variable
        echo "Added todo with ID: " . $undefinedId . "\n";
    }

    /**
     * Error: Calling undefined method on object
     */
    public function remove(int $id): bool
    {
        foreach ($this->todos as $index => $todo) {
            if ($todo->getId() === $id) {
                unset($this->todos[$index]);
                $this->todos = array_values($this->todos);
                $this->save();

                // PHPStan Level 0: undefined method
                $todo->logDeletion();

                return true;
            }
        }

        return false;
    }

    public function getNextId(): int
    {
        if (empty($this->todos)) {
            return 1;
        }

        $maxId = 0;
        foreach ($this->todos as $todo) {
            if ($todo->getId() > $maxId) {
                $maxId = $todo->getId();
            }
        }

        return $maxId + 1;
    }

    /**
     * Error: Accessing property on potentially null object
     */
    public function getCompletedCount(): int
    {
        $count = 0;
        foreach ($this->todos as $todo) {
            // PHPStan Level 0: won't catch this, but higher levels will
            if ($todo->isCompleted()) {
                $count++;
            }
        }

        // PHPStan Level 0: undefined property access
        return $this->completedTotal;
    }
}
