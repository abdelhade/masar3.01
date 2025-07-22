<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test Records</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #999;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #eee;
        }
    </style>
</head>
<body>
<form method="GET" action="{{ url('/test') }}">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="ابحث عن var1 أو var2">
    <button type="submit">بحث</button>
</form>

    <h1>Test Records</h1>
<h2>إجمالي Int1 = {{ $sumInt1 }}</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Int1</th>
                <th>Int2</th>
                <th>Var1</th>
                <th>Var2</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tests as $test)
                <tr>
                    <td>{{ $test->id }}</td>
                    <td>{{ $test->int1 }}</td>
                    <td>{{ $test->int2 }}</td>
                    <td>{{ $test->var1 }}</td>
                    <td>{{ $test->var2 }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
<div style="margin-top: 20px;">
{{ $tests->appends(['search' => request('search')])->links() }}
</div>
9
</body>
</html>
