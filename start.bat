@echo off
rem Menjalankan FrozeMate: MariaDB XAMPP di port 3307 + server Laravel di http://127.0.0.1:8000
rem (Port 3306 di mesin ini sudah dipakai MariaDB milik Dolibarr, jadi dipakai 3307.)
cd /d "%~dp0"

netstat -ano | findstr /C:":3307 " | findstr LISTENING >nul
if errorlevel 1 (
    echo Menyalakan MariaDB XAMPP di port 3307...
    start "FrozeMate DB (jangan ditutup)" /min C:\xampp\mysql\bin\mysqld.exe --defaults-file=C:\xampp\mysql\bin\my.ini --port=3307 --console
    timeout /t 4 /nobreak >nul
)

echo Buka http://127.0.0.1:8000  -  Ctrl+C untuk berhenti.
C:\xampp\php\php.exe artisan serve --host=127.0.0.1 --port=8000
