<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ config('app.name', 'Estetica ERP') }} — Área do Cliente</title>
    @php
        $hotPath = public_path('hot');
        $manifestPath = public_path('build/manifest.json');
        $viteReady = file_exists($hotPath) || file_exists($manifestPath);
    @endphp
    @if ($viteReady)
        @vite(['resources/scss/app.scss', 'resources/js/client/app.js'])
    @endif
  </head>
  <body class="bg-body-tertiary">
    @if ($viteReady)
        <div id="client-app"></div>
    @else
        <div style="min-height: 100vh" class="d-flex align-items-center justify-content-center text-center p-4 bg-white">
            <div class="w-100" style="max-width: 420px">
                <h1 class="h4 fw-bold mb-3">Frontend do cliente indisponível</h1>
                <p class="text-body-secondary mb-4">
                    Execute <code>npm run dev</code> ou <code>npm run build</code> para gerar os artefatos do Vite.
                </p>
            </div>
        </div>
    @endif
  </body>
</html>
