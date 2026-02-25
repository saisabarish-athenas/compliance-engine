<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview: {{ $form_title ?? $form_code }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .preview-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1rem; position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .preview-content { background: white; padding: 2rem; margin: 2rem auto; max-width: 1200px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        @media print { .preview-header, .no-print { display: none !important; } }
    </style>
</head>
<body>
    <div class="preview-header no-print">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">👁️ Preview: {{ $form_title ?? $form_code }}</h5>
                <small>Batch #{{ $batch_id }} | {{ \Carbon\Carbon::create($period_year, $period_month, 1)->format('F Y') }}</small>
            </div>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-light btn-sm">🖨️ Print</button>
                <button onclick="window.close()" class="btn btn-outline-light btn-sm">✖️ Close</button>
            </div>
        </div>
    </div>

    <div class="preview-content">
        @yield('content')
    </div>

    <div class="text-center py-4 no-print">
        <p class="text-muted"><small>This is a preview. Data is not saved to database.</small></p>
    </div>
</body>
</html>
