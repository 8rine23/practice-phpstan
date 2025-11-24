<?php

namespace App\Services;

use App\Models\Todo;
use App\Repositories\TodoRepository;
use Illuminate\Database\Eloquent\Collection;

class TodoService
{
    public function __construct(
        private TodoRepository $repository
    ) {}

    public function createTodo(array $data): Todo
    {
        return $this->repository->create($data);
    }

    public function updateTodo(int $id, array $data): bool
    {
        $todo = $this->repository->find($id);

        if (!$todo) {
            return false;
        }

        return $this->repository->update($todo, $data);
    }

    public function deleteTodo(int $id): bool
    {
        $todo = $this->repository->find($id);

        if (!$todo) {
            return false;
        }

        return $this->repository->delete($todo);
    }

    public function getTodo(int $id): ?Todo
    {
        return $this->repository->find($id);
    }

    public function getAllTodos(): Collection
    {
        return $this->repository->all();
    }

    public function completeTodo(int $id): bool
    {
        return $this->updateTodo($id, ['completed' => true]);
    }

    public function searchTodos(string $keyword): Collection
    {
        return $this->repository->search($keyword);
    }

    public function getStatistics(): array
    {
        $total = $this->repository->all()->count();
        $completed = $this->repository->getCompleted()->count();
        $pending = $this->repository->getPending()->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'pending' => $pending,
            'percentage' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Error: Accessing undefined variable
     * PHPStan Level 1: undefined variable
     * (Public method for demonstration - not used)
     */
    public function addUserIdToData(array $data): array
    {
        // Intentional error: $userId is not defined
        $data['user_id'] = $userId;

        return $data;
    }

    /**
     * Error: Calling undefined function
     * PHPStan Level 1: undefined function
     * (Public method for demonstration - not used)
     */
    public function calculateCompletionPercentage(int $completed, int $total): float
    {
        // Intentional error: calculatePercentage() function does not exist
        return calculatePercentage($completed, $total);
    }

    /**
     * Error: Accessing undefined static property
     * PHPStan Level 1: undefined static property
     * (Public method for demonstration - not used)
     */
    public function getExportFormat(): string
    {
        // Intentional error: Todo::$exportFormat does not exist
        return Todo::$exportFormat;
    }

    /**
     * NEW Error: Calling undefined function
     * PHPStan Level 1: undefined function
     * (Public method for demonstration - not used)
     */
    public function notifyUserAboutTodo(int $todoId): void
    {
        $todo = $this->repository->find($todoId);

        if ($todo) {
            // Intentional NEW error: notifyUser() function does not exist
            notifyUser($todo->title);
        }
    }

    /**
     * NEW Error: Instantiating undefined class
     * PHPStan Level 1: undefined class
     * (Public method for demonstration - not used)
     */
    public function createReport(): string
    {
        $todos = $this->repository->all();

        // Intentional NEW error: ReportGenerator class does not exist
        $generator = new ReportGenerator();

        return $generator->generate($todos);
    }
}
