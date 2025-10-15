<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ config('app.name', 'Estetica ERP') }}</title>
    @php
        $hotPath = public_path('hot');
        $manifestPath = public_path('build/manifest.json');
        $viteReady = file_exists($hotPath) || file_exists($manifestPath);
    @endphp
    @if ($viteReady)
        @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    @endif
  </head>
  <body class="bg-body-tertiary">
    @if ($viteReady)
        <div id="app"></div>
    @else
        <div style="min-height: 100vh" class="d-flex align-items-center justify-content-center text-center p-4 bg-white">
            <div class="w-100" style="max-width: 420px">
                <h1 class="h4 fw-bold mb-3">Frontend ainda nao inicializado</h1>
                <p class="text-body-secondary mb-4">
                    Para carregar a interface, execute <code>npm run dev</code> (modo desenvolvimento) ou
                    <code>npm run build</code> (modo producao) na raiz do projeto. Isso gera os artefatos do Vite.
                </p>
                <p class="small text-muted mb-0">
                    Depois disso, recarregue a pagina. Este aviso some automaticamente.
                </p>
            </div>
        </div>
    @endif
  </body>
</html>
