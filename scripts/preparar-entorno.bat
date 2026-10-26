@echo off
setlocal
if not exist "C:\xampp\php\php.exe" (
  echo ERROR: No se encontro C:\xampp\php\php.exe. Ajusta este script y Jenkinsfile si XAMPP esta en otra ruta.
  exit /b 1
)
"C:\xampp\php\php.exe" -v
if errorlevel 1 exit /b 1
"C:\xampp\php\php.exe" -m | findstr /i /x "PDO" >nul
if errorlevel 1 (
  echo ERROR: La extension PDO no esta habilitada en PHP.
  exit /b 1
)
"C:\xampp\php\php.exe" -m | findstr /i /x "pdo_mysql" >nul
if errorlevel 1 (
  echo ERROR: La extension pdo_mysql no esta habilitada en PHP.
  exit /b 1
)
echo Entorno PHP, PDO y pdo_mysql verificado.
exit /b 0
