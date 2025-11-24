<?php

namespace App\Http\Controllers;

use App\Services\ExportService;
use App\Services\StatisticsService;
use App\Services\TodoService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private TodoService $todoService,
        private StatisticsService $statisticsService,
        private ExportService $exportService
    ) {}

    public function index(): View
    {
        $todos = $this->todoService->getAllTodos();
        $statistics = $this->statisticsService->generate();

        return view('dashboard', compact('todos', 'statistics'));
    }

    public function export(): string
    {
        return $this->exportService->export($this->todoService->getAllTodos());
    }

    /**
     * Error: Calling undefined method
     * PHPStan Level 0: undefined method
     * (Method for demonstration - not used in routes)
     */
    public function progress(): array
    {
        $statistics = $this->statisticsService->generate();

        // Intentional error: calculateProgress() method does not exist
        return $this->calculateProgress($statistics);
    }

    /**
     * Error: Instantiating undefined class
     * PHPStan Level 0: undefined class
     * (Method for demonstration - not used in routes)
     */
    public function generateReport(): string
    {
        // Intentional error: ReportService class does not exist
        $reporter = new ReportService();

        return $reporter->generate($this->todoService->getAllTodos());
    }
}
