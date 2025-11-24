<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PHPStan Practice</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; }
        h1 { color: #333; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0; }
        .stat-card { background: #f5f5f5; padding: 20px; border-radius: 8px; }
        .stat-card h3 { margin: 0 0 10px 0; color: #666; font-size: 14px; }
        .stat-card .value { font-size: 32px; font-weight: bold; color: #333; }
        .todos-list { margin-top: 30px; }
        .todo-item { background: white; border: 1px solid #ddd; padding: 15px; margin-bottom: 10px; border-radius: 4px; }
        .nav { margin-bottom: 20px; }
        .nav a { margin-right: 15px; color: #007bff; text-decoration: none; }
        .nav a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="nav">
        <a href="/">Dashboard</a>
        <a href="/todos">Todos</a>
    </div>

    <h1>Dashboard</h1>

    <div class="stats">
        <div class="stat-card">
            <h3>Total Todos</h3>
            <div class="value">{{ $statistics['total_count'] }}</div>
        </div>
        <div class="stat-card">
            <h3>Completed</h3>
            <div class="value">{{ $statistics['completed_count'] }}</div>
        </div>
        <div class="stat-card">
            <h3>Pending</h3>
            <div class="value">{{ $statistics['pending_count'] }}</div>
        </div>
        <div class="stat-card">
            <h3>Completion Rate</h3>
            <div class="value">{{ $statistics['completion_rate'] }}%</div>
        </div>
    </div>

    <div class="todos-list">
        <h2>Recent Todos</h2>
        @forelse($todos->take(5) as $todo)
            <div class="todo-item">
                <strong>{{ $todo->title }}</strong>
                @if($todo->description)
                    <p>{{ $todo->description }}</p>
                @endif
                <small>Status: {{ $todo->completed ? 'Completed' : 'Pending' }}</small>
            </div>
        @empty
            <p>No todos yet. Create your first todo!</p>
        @endforelse
    </div>
</body>
</html>
