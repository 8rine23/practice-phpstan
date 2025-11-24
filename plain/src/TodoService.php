<?php

namespace App;

class TodoService
{
    /** @var TodoRepository */
    private $repository;

    public function __construct(TodoRepository $repository)
    {
        $this->repository = $repository;
    }

    public function createTodo(string $title, string $description = '', ?string $dueDate = null): Todo
    {
        $id = $this->repository->getNextId();
        $todo = new Todo($id, $title, $description, false, $dueDate);
        $this->repository->add($todo);

        return $todo;
    }

    public function listTodos(): array
    {
        return $this->repository->findAll();
    }

    public function completeTodo(int $id): bool
    {
        $todo = $this->repository->findById($id);

        if ($todo === null) {
            return false;
        }

        $todo->complete();
        $this->repository->save();

        return true;
    }

    public function deleteTodo(int $id): bool
    {
        return $this->repository->remove($id);
    }

    /**
     * Error: Calling method on potentially null object
     */
    public function getTodoTitle(int $id): string
    {
        $todo = $this->repository->findById($id);

        // PHPStan Level 0: won't catch this, higher levels will
        // But this is realistic code that developers write
        return $todo->getTitle();
    }

    /**
     * Error: Using undefined class
     */
    public function exportTodos(): string
    {
        $todos = $this->repository->findAll();

        // PHPStan Level 0: undefined class
        $exporter = new TodoExporter();
        return $exporter->export($todos);
    }

    /**
     * Error: Accessing undefined static property
     */
    public function getVersion(): string
    {
        // PHPStan Level 0: undefined static property
        return self::$version;
    }

    public function searchTodos(string $keyword): array
    {
        $results = [];
        $todos = $this->repository->findAll();

        foreach ($todos as $todo) {
            if (stripos($todo->getTitle(), $keyword) !== false ||
                stripos($todo->getDescription(), $keyword) !== false) {
                $results[] = $todo;
            }
        }

        return $results;
    }

    /**
     * Error: Accessing undefined function
     */
    public function printStatistics(): void
    {
        $todos = $this->repository->findAll();
        $completed = 0;
        $pending = 0;

        foreach ($todos as $todo) {
            if ($todo->isCompleted()) {
                $completed++;
            } else {
                $pending++;
            }
        }

        // PHPStan Level 0: undefined function
        formatStatistics($completed, $pending);
    }

    /**
     * Error: Possibly undefined variable
     * PHPStan Level 1: possibly undefined variable
     */
    public function getFirstTodoTitle(): string
    {
        $todos = $this->repository->findAll();

        if (count($todos) > 0) {
            $title = $todos[0]->getTitle();
        }

        // PHPStan Level 1: $title might be undefined
        return $title;
    }

    /**
     * Error: Too many arguments
     * PHPStan Level 1: too many arguments
     */
    public function createSimpleTodo(string $title): Todo
    {
        // createTodo expects 1-3 arguments, but we're passing 4
        // PHPStan Level 1: too many arguments
        return $this->createTodo($title, '', null, 'extra');
    }

    /**
     * Error: Possibly undefined variable in condition
     * PHPStan Level 1: possibly undefined variable
     */
    public function findTodoByTitle(string $searchTitle): ?Todo
    {
        $todos = $this->repository->findAll();

        foreach ($todos as $todo) {
            if ($todo->getTitle() === $searchTitle) {
                $found = $todo;
                break;
            }
        }

        // PHPStan Level 1: $found might be undefined if not found in loop
        return $found;
    }
}
