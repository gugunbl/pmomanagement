param(
    [Parameter(ValueFromRemainingArguments = $true)]
    [string[]] $ArtisanArguments
)

$projectRoot = $PSScriptRoot
$phpExecutable = Join-Path $projectRoot '.runtime\php83\php.exe'
$phpConfiguration = Join-Path $projectRoot '.runtime\php83\php.ini'

if (-not (Test-Path -LiteralPath $phpExecutable)) {
    throw 'PHP 8.3 runtime is missing from .runtime\php83.'
}

Push-Location $projectRoot

try {
    & $phpExecutable -c $phpConfiguration artisan @ArtisanArguments
    exit $LASTEXITCODE
}
finally {
    Pop-Location
}
