@echo off
set "UPLOAD_TMP_DIR=%~dp0..\..\tmp"

if exist "%UPLOAD_TMP_DIR%" (
    for %%I in ("%UPLOAD_TMP_DIR%") do set "UPLOAD_TMP_DIR=%%~fI"
) else (
    set "UPLOAD_TMP_DIR=%~dp0storage\app\tmp\uploads"
)

if not exist "%UPLOAD_TMP_DIR%" mkdir "%UPLOAD_TMP_DIR%"

if not exist "%UPLOAD_TMP_DIR%" (
    echo Failed to create PHP upload temporary directory: "%UPLOAD_TMP_DIR%"
    exit /b 1
)

set "TEMP=%UPLOAD_TMP_DIR%"
set "TMP=%UPLOAD_TMP_DIR%"

echo PHP 8.3 server running on [http://127.0.0.1:8000].
echo Upload temp directory: [%UPLOAD_TMP_DIR%]
echo Press Ctrl+C to stop the server.

"%~dp0.runtime\php83\php.exe" -c "%~dp0.runtime\php83\php.ini" -d "upload_tmp_dir=%UPLOAD_TMP_DIR%" -d "sys_temp_dir=%UPLOAD_TMP_DIR%" -d upload_max_filesize=64M -d post_max_size=70M -d memory_limit=256M -d max_execution_time=120 -S 127.0.0.1:8000 -t "%~dp0public" "%~dp0server.php"
