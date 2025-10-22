Param(
    [switch]$SkipTests
)

$ErrorActionPreference = 'Stop'

function Invoke-Step {
    param(
        [string]$Message,
        [scriptblock]$Command
    )

    Write-Host "`n>>> $Message" -ForegroundColor Cyan
    & $Command
}

try {
    Invoke-Step "Verificando mapeamento de regras versus testes" {
        php scripts/verify-rules-map.php
    }

    if (-not $SkipTests) {
        Invoke-Step "Executando suíte de testes Laravel (phpunit/pest)" {
            php artisan test
        }
    }

    Write-Host "`nTodos os checks executados com sucesso." -ForegroundColor Green
    exit 0
}
catch {
    Write-Host "`nPre-commit abortado: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}
