<!DOCTYPE html>
<html>
<head>
    <title>Form Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Form Test</h2>
        <form id="testForm" method="POST" action="#">
            @csrf
            <div class="mb-3">
                <label class="form-label">Test Input</label>
                <input type="text" name="test" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Submit Test</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('testForm');
            console.log('Test form found:', form);
            
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    console.log('Test form submitted successfully!');
                    alert('Test form works!');
                });
            }
        });
    </script>
</body>
</html>