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
    <h3>{{ __('modulos') }}</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>{{ __('modulo') }}</th>
                <th>{{ __('color') }}</th>
                <th>{{ __('icono') }}</th>
                <th>{{ __('ambito') }}</th>
                <th>{{ __('nivel') }}</th>
                <th>{{ __('descripcion') }}</th>
                <th>{{ __('alta') }}</th>
            </tr>
        </thead>
    
        <tbody>
            @foreach($rows as $u)
                <tr>
                    <td>{{ $u->id }}</td>
                    <td>{{ __($u->label) }}</td>
                    <td style="text-align: center;">
                        <span class="spot" style="border-radius: 50%; display: inline-block; height: 18px; vertical-align: middle;     width: 18px; background-color: {{ $u->color }} "></span>
                    </td>
                    <td>
                        la-{{ $u->icon }}
                    </td>
                    <td>{!! $scopes[$u->scope] !!}</td>
                    <td>{!! $levels[$u->level] !!}</td>
                    <td>{{ $u->description }}</td>
                    <td>{{ optional($u->created_at)->format('Y-m-d H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
