<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todos - PHPStan Practice</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; }
        h1 { color: #333; }
        .nav { margin-bottom: 20px; }
        .nav a { margin-right: 15px; color: #007bff; text-decoration: none; }
        .nav a:hover { text-decoration: underline; }
        .todo-item { background: white; border: 1px solid #ddd; padding: 15px; margin-bottom: 10px; border-radius: 4px; }
        .btn { padding: 8px 16px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="nav">
        <a href="/">Dashboard</a>
        <a href="/todos">Todos</a>
        <a href="/stats">Stats</a>
    </div>

    <h1>All Todos</h1>

    <a href="/todos/create" class="btn">Create New Todo</a>

    <div style="margin-top: 20px;">
        @forelse($todos as $todo)
            <div class="todo-item">
                <strong>{{ $todo->title }}</strong>
                @if($todo->description)
                    <p>{{ $todo->description }}</p>
                @endif
                <small>Status: {{ $todo->completed ? 'Completed' : 'Pending' }}</small>
                <br>
                <a href="/todos/{{ $todo->id }}/edit">Edit</a>
            </div>
        @empty
            <p>No todos yet. <a href="/todos/create">Create your first todo!</a></p>
        @endforelse
    </div>
</body>
</html>
