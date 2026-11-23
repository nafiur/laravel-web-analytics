<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Analytics — Setup required</title>
    <style>
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
               font-family: Inter, system-ui, sans-serif; background: #f7f8fc; color: #172033; }
        .card { max-width: 560px; background: #fff; border: 1px solid #e4e8f0; border-radius: 14px;
                padding: 32px; box-shadow: 0 4px 18px rgba(15,23,42,.05); }
        h1 { font-size: 18px; margin: 0 0 8px; }
        p { font-size: 13px; color: #667085; line-height: 1.6; }
        ul { font-size: 13px; color: #475467; padding-left: 18px; line-height: 1.8; }
        code { font-family: ui-monospace, monospace; background: #f1f4f9; padding: 2px 5px; border-radius: 4px; }
        .hint { margin-top: 18px; padding-top: 14px; border-top: 1px solid #e4e8f0; font-size: 12px; color: #667085; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Analytics database is not ready</h1>
        <p>The dashboard cannot load because the required tables have not been created yet. No stack trace is shown here — follow the steps below and reload.</p>
        <ul>
            @foreach($instructions as $instruction)
                <li>{{ $instruction }}</li>
            @endforeach
        </ul>
        <div class="hint">
            For a fresh install: run <code>php artisan migrate</code>. For the advanced schema, set
            <code>WEB_ANALYTICS_SCHEMA_ENABLED=true</code> before migrating, then run
            <code>php artisan lwa:upgrade --dry-run</code> to inspect legacy data.
        </div>
    </div>
</body>
</html>
