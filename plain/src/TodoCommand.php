<?php

namespace App;

class TodoCommand
{
    /** @var TodoService */
    private $service;

    public function __construct(TodoService $service)
    {
        $this->service = $service;
    }

    public function run(array $argv): void
    {
        if (count($argv) < 2) {
            $this->showHelp();
            return;
        }

        $command = $argv[1];

        switch ($command) {
            case 'add':
                $this->handleAdd($argv);
                break;
            case 'list':
                $this->handleList();
                break;
            case 'complete':
                $this->handleComplete($argv);
                break;
            case 'delete':
                $this->handleDelete($argv);
                break;
            case 'search':
                $this->handleSearch($argv);
                break;
            default:
                echo "Unknown command: $command\n";
                $this->showHelp();
        }
    }

    private function handleAdd(array $argv): void
    {
        if (!isset($argv[2])) {
            echo "Error: Title is required\n";
            return;
        }

        $title = $argv[2];
        $description = $argv[3] ?? '';
        $dueDate = $argv[4] ?? null;

        $todo = $this->service->createTodo($title, $description, $dueDate);
        echo "Todo created with ID: {$todo->getId()}\n";
    }

    private function handleList(): void
    {
        $todos = $this->service->listTodos();

        if (empty($todos)) {
            echo "No todos found\n";
            return;
        }

        foreach ($todos as $todo) {
            $status = $todo->isCompleted() ? '[✓]' : '[ ]';
            echo sprintf(
                "%s %d: %s\n",
                $status,
                $todo->getId(),
                $todo->getTitle()
            );

            if ($todo->getDescription()) {
                echo "   Description: {$todo->getDescription()}\n";
            }

            if ($todo->getDueDate()) {
                echo "   Due: {$todo->getDueDate()}\n";
            }
        }
    }

    private function handleComplete(array $argv): void
    {
        if (!isset($argv[2])) {
            echo "Error: Todo ID is required\n";
            return;
        }

        $id = (int)$argv[2];

        if ($this->service->completeTodo($id)) {
            echo "Todo $id marked as completed\n";
        } else {
            echo "Todo $id not found\n";
        }
    }

    private function handleDelete(array $argv): void
    {
        if (!isset($argv[2])) {
            echo "Error: Todo ID is required\n";
            return;
        }

        $id = (int)$argv[2];

        if ($this->service->deleteTodo($id)) {
            echo "Todo $id deleted\n";
        } else {
            echo "Todo $id not found\n";
        }
    }

    /**
     * Error: Accessing undefined variable
     */
    private function handleSearch(array $argv): void
    {
        if (!isset($argv[2])) {
            echo "Error: Search keyword is required\n";
            return;
        }

        $keyword = $argv[2];
        $results = $this->service->searchTodos($keyword);

        if (empty($results)) {
            echo "No todos found matching '$keyword'\n";
            return;
        }

        // PHPStan Level 0: undefined variable
        echo "Found $count results:\n";

        foreach ($results as $todo) {
            echo "- {$todo->getId()}: {$todo->getTitle()}\n";
        }
    }

    /**
     * Error: Calling undefined method
     */
    private function showHelp(): void
    {
        echo "Todo CLI Tool\n";
        echo "Usage: php todo.php <command> [arguments]\n\n";
        echo "Commands:\n";
        echo "  add <title> [description] [due_date]  - Add a new todo\n";
        echo "  list                                   - List all todos\n";
        echo "  complete <id>                          - Mark a todo as completed\n";
        echo "  delete <id>                            - Delete a todo\n";
        echo "  search <keyword>                       - Search todos\n";

        // PHPStan Level 0: undefined method
        $this->showVersion();
    }

    /**
     * Error: Accessing property on wrong object type
     */
    public function displayStatistics(): void
    {
        $todos = $this->service->listTodos();

        // PHPStan Level 0: might not catch this depending on context
        foreach ($todos as $todo) {
            // Typo in method name
            echo $todo->getID() . "\n"; // Should be getId()
        }
    }

    /**
     * New method: Calling undefined function
     */
    public function exportToJson(): string
    {
        $todos = $this->service->listTodos();

        // PHPStan Level 0: undefined function
        return convertToJson($todos);
    }

    /**
     * New method: Using undefined variable
     */
    public function printSummary(): void
    {
        // PHPStan Level 0: undefined variable
        echo "Total tasks: " . $totalTasks . "\n";
    }

    /**
     * New method: Instantiating undefined class
     */
    public function generateReport(): void
    {
        // PHPStan Level 0: undefined class
        $reporter = new TodoReporter();
        $reporter->generate();
    }

    /**
     * Error: Possibly undefined variable
     * PHPStan Level 1: possibly undefined variable
     */
    public function getSelectedTodo(array $argv): ?Todo
    {
        $todos = $this->service->listTodos();

        if (isset($argv[2])) {
            $id = (int)$argv[2];

            foreach ($todos as $todo) {
                if ($todo->getId() === $id) {
                    $selected = $todo;
                    break;
                }
            }
        }

        // PHPStan Level 1: $selected might be undefined
        return $selected;
    }

    /**
     * Error: Too many arguments passed to function
     * PHPStan Level 1: too many arguments
     */
    public function addTodoWithExtra(string $title, string $desc): void
    {
        // createTodo expects max 3 arguments, passing 5
        // PHPStan Level 1: too many arguments
        $this->service->createTodo($title, $desc, null, 'extra1', 'extra2');
    }

    /**
     * Error: Possibly undefined variable in complex condition
     * PHPStan Level 1: possibly undefined variable
     */
    public function processCommand(string $cmd): void
    {
        if ($cmd === 'stats') {
            $message = 'Showing statistics';
        } elseif ($cmd === 'export') {
            $message = 'Exporting data';
        }

        // PHPStan Level 1: $message might be undefined if $cmd is neither 'stats' nor 'export'
        echo $message . "\n";
    }
}
