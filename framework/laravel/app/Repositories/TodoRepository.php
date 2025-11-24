<?php

namespace App\Repositories;

use App\Models\Todo;
use Illuminate\Database\Eloquent\Collection;

class TodoRepository
{
    public function all(): Collection
    {
        return Todo::all();
    }

    public function find(int $id): ?Todo
    {
        return Todo::find($id);
    }

    public function create(array $data): Todo
    {
        return Todo::create($data);
    }

    public function update(Todo $todo, array $data): bool
    {
        return $todo->update($data);
    }

    public function delete(Todo $todo): bool
    {
        return $todo->delete();
    }

    public function getCompleted(): Collection
    {
        return Todo::where('completed', true)->get();
    }

    public function getPending(): Collection
    {
        return Todo::where('completed', false)->get();
    }

    public function search(string $keyword): Collection
    {
        return Todo::where('title', 'like', "%{$keyword}%")
            ->orWhere('description', 'like', "%{$keyword}%")
            ->get();
    }
}
