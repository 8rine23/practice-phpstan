<?php

namespace App\Services;

use App\Repositories\TodoRepository;

class StatisticsService
{
    public function __construct(
        private TodoRepository $repository
    ) {}

    public function generate(): array
    {
        $todos = $this->repository->all();
        $completed = $this->repository->getCompleted();
        $pending = $this->repository->getPending();

        return [
            'total_count' => $todos->count(),
            'completed_count' => $completed->count(),
            'pending_count' => $pending->count(),
            'completion_rate' => $this->calculateCompletionRate($todos->count(), $completed->count()),
        ];
    }

    /**
     * Error: Accessing undefined property
     * PHPStan Level 1: undefined property
     */
    public function getCachedData(): mixed
    {
        // Intentional error: $this->cache does not exist
        return $this->cache;
    }

    /**
     * Error: Calling undefined method
     * PHPStan Level 1: undefined method
     */
    public function refresh(): void
    {
        // Intentional error: clearCache() method does not exist
        $this->clearCache();
    }

    private function calculateCompletionRate(int $total, int $completed): float
    {
        if ($total === 0) {
            return 0.0;
        }

        return round(($completed / $total) * 100, 2);
    }

    /**
     * NEW Error: Accessing undefined variable
     * PHPStan Level 1: undefined variable
     */
    public function getWeeklyStats(): array
    {
        $todos = $this->repository->all();

        // Intentional NEW error: $weekData is not defined
        return [
            'weekly_total' => $todos->count(),
            'weekly_data' => $weekData,
        ];
    }
}
