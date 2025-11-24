<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Todo - PHPStan Practice</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; }
        h1 { color: #333; }
        .nav { margin-bottom: 20px; }
        .nav a { margin-right: 15px; color: #007bff; text-decoration: none; }
        .nav a:hover { text-decoration: underline; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .form-group textarea { min-height: 100px; }
        .btn { padding: 8px 16px; background: #007bff; color: white; text-decoration: none; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #0056b3; }
        .btn-secondary { background: #6c757d; margin-left: 10px; }
        .btn-secondary:hover { background: #5a6268; }
        .btn-danger { background: #dc3545; margin-left: 10px; }
        .btn-danger:hover { background: #c82333; }
        .form-actions { display: flex; align-items: center; }
    </style>
</head>
<body>
    <div class="nav">
        <a href="/">Dashboard</a>
        <a href="/todos">Todos</a>
        <a href="/stats">Stats</a>
    </div>

    <h1>Edit Todo</h1>

    <form action="/todos/{{ $todo->id }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" value="{{ $todo->title }}" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description">{{ $todo->description }}</textarea>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="completed" value="1" {{ $todo->completed ? 'checked' : '' }}>
                Mark as completed
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn">Update Todo</button>
            <a href="/todos" class="btn btn-secondary">Cancel</a>
        </div>
    </form>

    <form action="/todos/{{ $todo->id }}" method="POST" style="margin-top: 20px;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this todo?')">Delete Todo</button>
    </form>
</body>
</html>
