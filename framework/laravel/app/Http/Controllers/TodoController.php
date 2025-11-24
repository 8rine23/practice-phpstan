<?php

namespace App\Http\Controllers;

use App\Services\TodoService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TodoController extends Controller
{
    public function __construct(
        private TodoService $todoService
    ) {}

    public function index(): View
    {
        $todos = $this->todoService->getAllTodos();

        return view('todos.index', compact('todos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->todoService->createTodo($request->all());

        return redirect()->route('todos.index');
    }

    public function create(): View
    {
        return view('todos.create');
    }

    public function edit(int $id): View
    {
        $todo = $this->todoService->getTodo($id);

        return view('todos.edit', compact('todo'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->todoService->updateTodo($id, $request->all());

        return redirect()->route('todos.index');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->todoService->deleteTodo($id);

        return redirect()->route('todos.index');
    }

    public function stats(): View
    {
        $statistics = $this->todoService->getStatistics();

        return view('todos.stats', compact('statistics'));
    }

    /**
     * Error: Accessing undefined variable
     * PHPStan Level 1: undefined variable
     * (Method for demonstration - not used in routes)
     */
    public function sortTodosByCustomOrder(): void
    {
        $todos = $this->todoService->getAllTodos();

        // Intentional error: $sortOrder is not defined
        $sorted = $todos->sortBy($sortOrder);
    }

    /**
     * Error: Calling undefined method
     * PHPStan Level 1: undefined method
     * (Method for demonstration - not used in routes)
     */
    public function validateTodoTitle(string $title): void
    {
        // Intentional error: validateTitle() method does not exist
        $this->validateTitle($title);
    }

    /**
     * Error: Accessing undefined property
     * PHPStan Level 1: undefined property
     * (Method for demonstration - not used in routes)
     */
    public function getTotalCompleted(): int
    {
        // Intentional error: $this->totalCompleted does not exist
        return $this->totalCompleted;
    }

    /**
     * NEW Error: Accessing undefined variable
     * PHPStan Level 1: undefined variable
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);

        foreach ($ids as $id) {
            $this->todoService->deleteTodo($id);
        }

        // Intentional NEW error: $deletedCount is not defined
        return redirect()->route('todos.index')->with('message', "Deleted {$deletedCount} todos");
    }

    /**
     * NEW Error: Calling undefined method
     * PHPStan Level 1: undefined method
     */
    public function archive(): RedirectResponse
    {
        // Intentional NEW error: archiveOldTodos() method does not exist
        $archived = $this->archiveOldTodos();

        return redirect()->route('todos.index')->with('message', "Archived {$archived} todos");
    }

    /**
     * NEW Error: Accessing undefined property
     * PHPStan Level 1: undefined property
     */
    public function restore(int $id): RedirectResponse
    {
        $this->todoService->updateTodo($id, ['completed' => false]);

        // Intentional NEW error: $this->restoredItems does not exist
        $this->restoredItems++;

        return redirect()->route('todos.index');
    }
}
