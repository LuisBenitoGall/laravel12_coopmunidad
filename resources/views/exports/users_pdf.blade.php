<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width:100%; border-collapse: collapse; }
        th, td { border:1px solid #ccc; padding:6px; }
        th { background:#eee; text-align:left; }
    </style>
</head>

<body>
    <h3>{{ __('usuarios') }}</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>{{ __('nombre') }}</th>
                <th>{{ __('apellidos') }}</th>
                <th>{{ __('email') }}</th>
                <th>{{ __('telefonos') }}</th>
                <th>{{ __('alta') }}</th>
            </tr>
        </thead>
    
        <tbody>
            @foreach($rows as $u)
                <tr>
                    <td>{{ $u->id }}</td>
                    <td>{{ $u->name }}</td>
                    <td>{{ $u->surname }}</td>
                    <td>{{ $u->email }}</td>
                    <td>{{ collect($u->phones ?? [])->pluck('phone_number')->implode(', ') }}</td>
                    <td>{{ optional($u->created_at)->format('Y-m-d H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
