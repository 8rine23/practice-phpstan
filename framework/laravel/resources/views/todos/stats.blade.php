<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics - PHPStan Practice</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; background: #f5f5f5; }
        h1 { color: #333; }
        .nav { margin-bottom: 20px; }
        .nav a { margin-right: 15px; color: #007bff; text-decoration: none; }
        .nav a:hover { text-decoration: underline; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stat-card h3 { margin: 0 0 10px 0; color: #666; font-size: 14px; text-transform: uppercase; }
        .stat-card .value { font-size: 36px; font-weight: bold; color: #333; }
        .stat-card.total .value { color: #007bff; }
        .stat-card.completed .value { color: #28a745; }
        .stat-card.pending .value { color: #ffc107; }
        .stat-card.percentage .value { color: #17a2b8; }
        .stat-card .label { font-size: 12px; color: #999; margin-top: 5px; }
        .back-link { display: inline-block; margin-top: 20px; color: #007bff; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="nav">
        <a href="/">Dashboard</a>
        <a href="/todos">Todos</a>
        <a href="/stats">Stats</a>
    </div>

    <h1>Todo Statistics</h1>

    <div class="stats-grid">
        <div class="stat-card total">
            <h3>Total Todos</h3>
            <div class="value">{{ $statistics['total'] }}</div>
            <div class="label">All tasks in the system</div>
        </div>

        <div class="stat-card completed">
            <h3>Completed</h3>
            <div class="value">{{ $statistics['completed'] }}</div>
            <div class="label">Tasks marked as done</div>
        </div>

        <div class="stat-card pending">
            <h3>Pending</h3>
            <div class="value">{{ $statistics['pending'] }}</div>
            <div class="label">Tasks still in progress</div>
        </div>

        <div class="stat-card percentage">
            <h3>Completion Rate</h3>
            <div class="value">{{ $statistics['percentage'] }}%</div>
            <div class="label">Overall progress</div>
        </div>
    </div>

    <a href="/todos" class="back-link">← Back to Todos</a>
</body>
</html>
