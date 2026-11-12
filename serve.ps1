param(
    [string] $HostAddress = '127.0.0.1',
    [ValidateRange(1, 65535)]
    [int] $Port = 8000
)

$projectRoot = $PSScriptRoot
$phpExecutable = Join-Path $projectRoot '.runtime\php83\php.exe'
$phpConfiguration = Join-Path $projectRoot '.runtime\php83\php.ini'
$xamppTemporaryDirectory = [System.IO.Path]::GetFullPath((Join-Path $projectRoot '..\..\tmp'))
$uploadTemporaryDirectory = if (Test-Path -LiteralPath $xamppTemporaryDirectory -PathType Container) {
    $xamppTemporaryDirectory
} else {
    Join-Path $projectRoot 'storage\app\tmp\uploads'
}

if (-not (Test-Path -LiteralPath $phpExecutable)) {
    throw 'PHP 8.3 runtime is missing from .runtime\php83.'
}

New-Item -ItemType Directory -Force -Path $uploadTemporaryDirectory | Out-Null

if (-not (Test-Path -LiteralPath $uploadTemporaryDirectory -PathType Container)) {
    throw "Unable to create PHP upload temporary directory: $uploadTemporaryDirectory"
}

$env:TEMP = $uploadTemporaryDirectory
$env:TMP = $uploadTemporaryDirectory

Push-Location $projectRoot

try {
    Write-Host "PHP 8.3 server running on [http://${HostAddress}:$Port]."
    Write-Host "Upload temp directory: [$uploadTemporaryDirectory]"
    Write-Host 'Press Ctrl+C to stop the server.'

    & $phpExecutable -c $phpConfiguration `
        -d "upload_tmp_dir=$uploadTemporaryDirectory" `
        -d "sys_temp_dir=$uploadTemporaryDirectory" `
        -d upload_max_filesize=64M `
        -d post_max_size=70M `
        -d memory_limit=256M `
        -d max_execution_time=120 `
        -S "${HostAddress}:$Port" `
        -t (Join-Path $projectRoot 'public') `
        (Join-Path $projectRoot 'server.php')
    exit $LASTEXITCODE
}
finally {
    Pop-Location
}
