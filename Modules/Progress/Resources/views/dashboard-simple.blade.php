<!DOCTYPE html>
<html>
<head>
    <title>Progress Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="alert alert-success">
            <h1>✅ Progress Module is Working!</h1>
            <p>You have successfully accessed the Progress module dashboard.</p>
            <hr>
            <p><strong>User:</strong> {{ auth()->user()->name }}</p>
            <p><strong>Route:</strong> {{ request()->url() }}</p>
            <p><strong>Module:</strong> Progress</p>
            <hr>
            <a href="{{ route('progress.projects.index') }}" class="btn btn-primary">Go to Projects</a>
            <a href="{{ route('progress.dashboard') }}" class="btn btn-secondary">Back to Main Dashboard</a>
        </div>
    </div>
</body>
</html>
